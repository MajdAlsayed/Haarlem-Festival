<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\PaymentConfig;
use App\Core\Session;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketRepository;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\StripePaymentService;
use App\Services\TicketAvailabilityService;

/**
 * Checkout: confirmation page, demo and Stripe payment, pay-later reserve, and completing pending orders from account.
 */
final class CheckoutController
{
    public function show(): void
    {
        $userId = $this->requireLoginOrRedirect();

        $cartService = $this->cartInfrastructure()['cartService'];
        $vm = $cartService->getCurrentCart();

        if ($vm->cartId === null || $vm->items === []) {
            header('Location: /cart');
            exit;
        }

        $app = (new SettingsRepository())->getAll();
        $error = Session::getFlash('checkout_error');
        $csrf = Csrf::token('checkout');
        // €0 cart → no Stripe button (class demo still uses “Confirm without payment”).
        $stripeOn = StripePaymentService::isConfigured() && $vm->total > 0;
        $demoOn = true;

        require __DIR__ . '/../Views/Checkout/confirm.php';
    }

    /** Demo / free-only: instant paid order (no Stripe). */
    public function pay(): void
    {
        $userId = $this->requireLoginOrRedirect();

        if (!Csrf::validate('checkout', $_POST['_csrf'] ?? null)) {
            Session::setFlash('checkout_error', 'Invalid security token. Try again.');
            header('Location: /checkout');
            exit;
        }

        $checkout = $this->makeCheckoutService();

        try {
            $orderId = $checkout->completePurchase($userId);
            header('Location: /checkout/success?order_id=' . $orderId);
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('checkout_error', $e->getMessage());
            header('Location: /checkout');
            exit;
        }
    }

    /** Real payment: redirect to Stripe Checkout (card + iDEAL). */
    public function payStripe(): void
    {
        $userId = $this->requireLoginOrRedirect();

        if (!Csrf::validate('checkout', $_POST['_csrf'] ?? null)) {
            Session::setFlash('checkout_error', 'Invalid security token. Try again.');
            header('Location: /checkout');
            exit;
        }

        if (!StripePaymentService::isConfigured()) {
            Session::setFlash('checkout_error', 'Stripe is not configured.');
            header('Location: /checkout');
            exit;
        }

        $stack = $this->cartInfrastructure();
        $vm = $stack['cartService']->getCurrentCart();
        if ($vm->cartId === null || $vm->items === [] || $vm->total <= 0) {
            Session::setFlash('checkout_error', 'Cart must not be empty for card/iDEAL payment.');
            header('Location: /checkout');
            exit;
        }

        try {
            $stack['availability']->assertCartCanCheckout($vm);
            $base = PaymentConfig::publicBaseUrl();
            $url = StripePaymentService::createCheckoutSession($vm, $userId, $base);
            header('Location: ' . $url);
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('checkout_error', $e->getMessage());
            header('Location: /checkout');
            exit;
        }
    }

    /** Hold cart as unpaid order (24h window); customer completes payment from account. */
    public function payLater(): void
    {
        $userId = $this->requireLoginOrRedirect();

        if (!Csrf::validate('checkout', $_POST['_csrf'] ?? null)) {
            Session::setFlash('checkout_error', 'Invalid security token. Try again.');
            header('Location: /checkout');
            exit;
        }

        $checkout = $this->makeCheckoutService();

        try {
            $orderId = $checkout->reservePayLater($userId);
            header('Location: /account/order/' . $orderId);
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('checkout_error', $e->getMessage());
            header('Location: /checkout');
            exit;
        }
    }

    /** Demo: complete a pay-later order without Stripe. */
    public function payPending(): void
    {
        $userId = $this->requireLoginOrRedirect();

        if (!Csrf::validate('checkout', $_POST['_csrf'] ?? null)) {
            Session::setFlash('order_error', 'Invalid security token. Try again.');
            header('Location: /account/orders');
            exit;
        }

        $orderId = (int) ($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            Session::setFlash('order_error', 'Invalid order.');
            header('Location: /account/orders');
            exit;
        }

        $checkout = $this->makeCheckoutService();

        try {
            $checkout->completePendingPaymentDemo($orderId, $userId);
            header('Location: /checkout/success?order_id=' . $orderId);
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('order_error', $e->getMessage());
            header('Location: /account/order/' . $orderId);
            exit;
        }
    }

    /** Stripe Checkout for a pending (pay-later) order. */
    public function payPendingStripe(): void
    {
        $userId = $this->requireLoginOrRedirect();

        if (!Csrf::validate('checkout', $_POST['_csrf'] ?? null)) {
            Session::setFlash('order_error', 'Invalid security token. Try again.');
            header('Location: /account/orders');
            exit;
        }

        $orderId = (int) ($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            Session::setFlash('order_error', 'Invalid order.');
            header('Location: /account/orders');
            exit;
        }

        if (!StripePaymentService::isConfigured()) {
            Session::setFlash('order_error', 'Stripe is not configured.');
            header('Location: /account/order/' . $orderId);
            exit;
        }

        $orders = new OrderRepository();
        $order = $orders->findForCustomer($orderId, $userId);
        if ($order === null || ($order['status'] ?? '') !== 'pending') {
            Session::setFlash('order_error', 'This order is not waiting for payment.');
            header('Location: /account/order/' . $orderId);
            exit;
        }

        if (empty($order['expires_at'])) {
            Session::setFlash('order_error', 'This order cannot be paid online.');
            header('Location: /account/order/' . $orderId);
            exit;
        }

        $total = (float) ($order['total_amount'] ?? 0);
        if ($total <= 0) {
            Session::setFlash('order_error', 'Use demo payment for free-only orders.');
            header('Location: /account/order/' . $orderId);
            exit;
        }

        try {
            $lines = $orders->getOrderLineItemsForInvoice($orderId);
            $base = PaymentConfig::publicBaseUrl();
            $url = StripePaymentService::createCheckoutSessionForPendingOrder(
                $orderId,
                $userId,
                $total,
                $lines,
                $base
            );
            header('Location: ' . $url);
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('order_error', $e->getMessage());
            header('Location: /account/order/' . $orderId);
            exit;
        }
    }

    public function cancel(): void
    {
        $this->requireLoginOrRedirect();
        Session::setFlash('checkout_error', 'Payment was cancelled. You can try again when ready.');
        header('Location: /checkout');
        exit;
    }

    public function success(): void
    {
        $userId = $this->requireLoginOrRedirect();

        // Stripe redirect: ?session_id=… → we finalize the order then redirect again with ?order_id= for a clean URL.
        $stripeSessionId = trim((string) ($_GET['session_id'] ?? ''));
        if ($stripeSessionId !== '') {
            if (!StripePaymentService::isConfigured()) {
                header('Location: /cart');
                exit;
            }
            $checkout = $this->makeCheckoutService();
            try {
                $orderId = $checkout->completeAfterStripe($stripeSessionId, $userId);
                header('Location: /checkout/success?order_id=' . $orderId);
                exit;
            } catch (\Throwable $e) {
                Session::setFlash('checkout_error', $e->getMessage());
                header('Location: /checkout');
                exit;
            }
        }

        $orderId = (int) ($_GET['order_id'] ?? 0);
        if ($orderId <= 0) {
            header('Location: /cart');
            exit;
        }

        $orders = new OrderRepository();
        $order = $orders->findForCustomer($orderId, $userId);
        if ($order === null) {
            http_response_code(404);
            echo 'Order not found.';
            exit;
        }

        $tickets = $orders->getTicketCodesForOrder($orderId);
        $app = (new SettingsRepository())->getAll();
        $paidWithStripe = !empty($order['stripe_checkout_session_id']);

        require __DIR__ . '/../Views/Checkout/success.php';
    }

    /**
     * Shared stack for checkout: one CartRepository instance so cart lines and capacity math stay consistent.
     *
     * @return array{cartRepo: CartRepository, ticketRepo: TicketRepository, availability: TicketAvailabilityService, cartService: CartService}
     */
    private function cartInfrastructure(): array
    {
        $cartRepo = new CartRepository();
        $ticketRepo = new TicketRepository();
        $availability = new TicketAvailabilityService($cartRepo, $ticketRepo);

        return [
            'cartRepo' => $cartRepo,
            'ticketRepo' => $ticketRepo,
            'availability' => $availability,
            'cartService' => new CartService($cartRepo, $availability),
        ];
    }

    private function makeCheckoutService(): CheckoutService
    {
        $i = $this->cartInfrastructure();

        return new CheckoutService(
            $i['cartService'],
            $i['cartRepo'],
            new OrderRepository(),
            $i['ticketRepo'],
            $i['availability']
        );
    }

    private function requireLoginOrRedirect(): int
    {
        $uid = isset($_SESSION['auth']['user_id']) ? (int) $_SESSION['auth']['user_id'] : 0;
        if ($uid <= 0) {
            header('Location: /login?return=/checkout');
            exit;
        }

        return $uid;
    }
}
