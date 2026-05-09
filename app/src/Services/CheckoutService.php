<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Repositories\TicketRepository;

/** Cart and pay-later checkout: create paid orders and ticket rows; triggers confirmation email. */
final class CheckoutService
{
    public function __construct(
        private CartService $cartService,
        private CartRepository $cartRepository,
        private OrderRepository $orderRepository,
        private TicketRepository $ticketRepository,
        private TicketAvailabilityService $availability
    ) {
    }

    /** Instant paid order (no payment gateway — local / class demo). */
    public function completePurchase(int $userId): int
    {
        return $this->finalizeOrder($userId, null);
    }

    public function completeAfterStripe(string $stripeSessionId, int $userId): int
    {
        // Stripe (or the user) can hit the success URL twice. If we already stored this session on the order, bail out
        // and return the same order id so we do not create tickets twice.
        $existing = $this->orderRepository->findOrderIdByStripeSessionId($stripeSessionId);
        if ($existing !== null) {
            return $existing;
        }

        $data = StripePaymentService::retrieveSession($stripeSessionId);
        if ($data['payment_status'] !== 'paid') {
            throw new \RuntimeException('Payment was not completed.');
        }

        // Pay-later checkout puts pending_order_id in metadata instead of cart_id — totally different path from normal cart Stripe.
        $pendingOid = (int) ($data['metadata']['pending_order_id'] ?? 0);
        if ($pendingOid > 0) {
            return $this->completePendingAfterStripe($pendingOid, $userId, $stripeSessionId, $data);
        }

        $uid = (int) ($data['metadata']['user_id'] ?? 0);
        $cid = (int) ($data['metadata']['cart_id'] ?? 0);
        if ($uid !== $userId) {
            throw new \RuntimeException('Payment session does not match your account.');
        }

        $cartVm = $this->cartService->getCurrentCart();
        if ($cartVm->cartId === null || $cartVm->items === []) {
            throw new \RuntimeException('Your cart is empty.');
        }
        if ($cartVm->cartId !== $cid) {
            throw new \RuntimeException('Your cart changed during payment. Contact support if you were charged.');
        }

        $expectedCents = (int) round($cartVm->total * 100);
        if ($expectedCents !== $data['amount_total']) {
            throw new \RuntimeException('Amount mismatch. Do not retry; check your order history or contact support.');
        }

        return $this->finalizeOrder($userId, $stripeSessionId);
    }

    /**
     * Reserve cart as pending (24h). Cart is cleared; customer pays from order detail.
     *
     * @return int New order id
     */
    public function reservePayLater(int $userId): int
    {
        $cartVm = $this->cartService->getCurrentCart();
        if ($cartVm->cartId === null || $cartVm->items === []) {
            throw new \RuntimeException('Your cart is empty.');
        }

        $total = $cartVm->total;
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $this->availability->assertCartCanCheckout($cartVm);
            $orderId = $this->orderRepository->createPendingOrder($userId, $total);

            foreach ($cartVm->items as $item) {
                $lineTotal = $item->getLineTotal();
                $this->orderRepository->insertOrderItem(
                    $orderId,
                    $item->ticketDetailsId,
                    $item->quantity,
                    $item->price,
                    $lineTotal
                );
            }

            $this->cartRepository->deleteAllItemsForCart($cartVm->cartId);
            $this->cartRepository->markCartConverted($cartVm->cartId);
            unset($_SESSION['cart_id']);

            $db->commit();

            try {
                (new OrderConfirmationMailer())->sendPendingReservation($orderId, $userId);
            } catch (\Throwable) {
                // Mail is nice-to-have; never roll back the reservation if logging or mail() blows up.
            }

            return $orderId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function completePendingPaymentDemo(int $orderId, int $userId): void
    {
        $this->fulfillPendingOrderCore($orderId, $userId, null);
    }

    /**
     * @param array{payment_status:string, amount_total:int, metadata:array<string,string>} $data
     */
    private function completePendingAfterStripe(int $pendingOrderId, int $userId, string $stripeSessionId, array $data): int
    {
        $uid = (int) ($data['metadata']['user_id'] ?? 0);
        if ($uid !== $userId) {
            throw new \RuntimeException('Payment session does not match your account.');
        }

        $order = $this->orderRepository->findForCustomer($pendingOrderId, $userId);
        if ($order === null || ($order['status'] ?? '') !== 'pending') {
            throw new \RuntimeException('This order is not waiting for payment.');
        }

        $expectedCents = (int) round((float) ($order['total_amount'] ?? 0) * 100);
        if ($expectedCents !== $data['amount_total']) {
            throw new \RuntimeException('Amount mismatch for this order. Do not retry; contact support if you were charged.');
        }

        $this->fulfillPendingOrderCore($pendingOrderId, $userId, $stripeSessionId);

        return $pendingOrderId;
    }

    private function fulfillPendingOrderCore(int $orderId, int $userId, ?string $stripeCheckoutSessionId): void
    {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            // FOR UPDATE stops two tabs from both issuing tickets for the same pending order.
            $locked = $this->orderRepository->lockPendingOrderForPay($orderId, $userId);
            if ($locked === null) {
                throw new \RuntimeException('This reservation has expired or was already paid.');
            }

            // Admin could shrink capacity after they reserved; better fail than oversell.
            $this->assertPendingFulfillmentCapacity($orderId);

            $lines = $this->orderRepository->getOrderFulfillmentLines($orderId);
            foreach ($lines as $line) {
                // One ticket row per seat (same idea as instant checkout).
                for ($n = 0; $n < $line['quantity']; $n++) {
                    $this->ticketRepository->createForOrderItem($line['order_item_id']);
                }
            }

            $this->orderRepository->markOrderPaidAndClearPendingWindow($orderId, $stripeCheckoutSessionId);

            $db->commit();

            try {
                (new OrderConfirmationMailer())->send($orderId, $userId);
            } catch (\Throwable) {
                // Do not fail fulfillment if logging or mail() errors; order is already paid in DB.
            }
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private function assertPendingFulfillmentCapacity(int $orderId): void
    {
        foreach ($this->orderRepository->getOrderFulfillmentLines($orderId) as $line) {
            $tid = $line['ticket_details_id'];
            $cap = $this->cartRepository->getTicketDetailsCapacity($tid);
            if ($cap === null) {
                continue;
            }
            $sold = $this->ticketRepository->countSoldForTicketDetails($tid);
            $cart = $this->cartRepository->sumActiveCartQuantityForTicketDetails($tid);
            // Includes this order’s lines too — that is intentional: total demand must still fit the room.
            $pending = $this->cartRepository->sumPendingOrderQuantityForTicketDetails($tid);
            if ($sold + $cart + $pending > $cap) {
                throw new \RuntimeException(
                    'Capacity changed and this reservation can no longer be fulfilled. Please contact the festival desk.'
                );
            }
        }
    }

    private function finalizeOrder(int $userId, ?string $stripeCheckoutSessionId): int
    {
        $cartVm = $this->cartService->getCurrentCart();
        if ($cartVm->cartId === null || $cartVm->items === []) {
            throw new \RuntimeException('Your cart is empty.');
        }

        $total = $cartVm->total;
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $this->availability->assertCartCanCheckout($cartVm);

            $orderId = $this->orderRepository->createPaidOrder($userId, $total, $stripeCheckoutSessionId);

            foreach ($cartVm->items as $item) {
                $lineTotal = $item->getLineTotal();
                $orderItemId = $this->orderRepository->insertOrderItem(
                    $orderId,
                    $item->ticketDetailsId,
                    $item->quantity,
                    $item->price,
                    $lineTotal
                );

                for ($n = 0; $n < $item->quantity; $n++) {
                    $this->ticketRepository->createForOrderItem($orderItemId);
                }
            }

            $this->cartRepository->deleteAllItemsForCart($cartVm->cartId);
            $this->cartRepository->markCartConverted($cartVm->cartId);
            unset($_SESSION['cart_id']);

            $db->commit();

            try {
                (new OrderConfirmationMailer())->send($orderId, $userId);
            } catch (\Throwable) {
                // Same as pay-later: checkout succeeded in DB; don’t fail the user because mail broke.
            }

            return $orderId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
