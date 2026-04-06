<?php

declare(strict_types=1);

namespace App\Services;

use App\ViewModels\CartViewModel;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/** Stripe Checkout: cart checkout and pay-later completion (session metadata distinguishes the flow). */
final class StripePaymentService
{
    public static function secretKey(): ?string
    {
        $k = getenv('STRIPE_SECRET_KEY');

        return is_string($k) && $k !== '' ? $k : null;
    }

    public static function isConfigured(): bool
    {
        return self::secretKey() !== null;
    }

    /**
     * @return string Redirect URL to Stripe-hosted payment page
     */
    public static function createCheckoutSession(CartViewModel $vm, int $userId, string $publicBaseUrl): string
    {
        $secret = self::secretKey();
        if ($secret === null) {
            throw new \RuntimeException('Stripe is not configured.');
        }

        if ($vm->cartId === null || $vm->items === []) {
            throw new \RuntimeException('Cart is empty.');
        }

        if ($vm->total <= 0) {
            throw new \RuntimeException('Use demo checkout for free-only orders.');
        }

        Stripe::setApiKey($secret);

        $base = rtrim($publicBaseUrl, '/');
        $lineItems = [];
        foreach ($vm->items as $item) {
            $name = mb_substr($item->name, 0, 120);
            $lineItems[] = [
                'quantity' => $item->quantity,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) round($item->price * 100),
                    'product_data' => [
                        'name' => $name,
                    ],
                ],
            ];
        }

        // Metadata is echoed back when the customer returns; CheckoutService matches user + cart before finalizing.
        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card', 'ideal'],
            'line_items' => $lineItems,
            'success_url' => $base . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $base . '/checkout/cancel',
            'metadata' => [
                'user_id' => (string) $userId,
                'cart_id' => (string) $vm->cartId,
            ],
            'client_reference_id' => 'cart_' . $vm->cartId,
        ]);

        $url = $session->url;
        if ($url === null) {
            throw new \RuntimeException('Stripe did not return a checkout URL.');
        }

        return $url;
    }

    /**
     * Same hosted checkout as the cart flow, but line items come from order_lines and metadata says which pending
     * order to close when Stripe sends the user back (see CheckoutService::completeAfterStripe).
     *
     * @param list<array{name:string,quantity:int,unit_price:string}> $lines
     */
    public static function createCheckoutSessionForPendingOrder(
        int $orderId,
        int $userId,
        float $totalAmount,
        array $lines,
        string $publicBaseUrl
    ): string {
        $secret = self::secretKey();
        if ($secret === null) {
            throw new \RuntimeException('Stripe is not configured.');
        }

        if ($lines === [] || $totalAmount <= 0) {
            throw new \RuntimeException('Invalid order for Stripe checkout.');
        }

        Stripe::setApiKey($secret);

        $base = rtrim($publicBaseUrl, '/');
        $lineItems = [];
        foreach ($lines as $item) {
            $name = mb_substr((string) $item['name'], 0, 120);
            $qty = max(1, (int) $item['quantity']);
            $unit = (float) $item['unit_price'];
            $lineItems[] = [
                'quantity' => $qty,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) round($unit * 100),
                    'product_data' => [
                        'name' => $name,
                    ],
                ],
            ];
        }

        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card', 'ideal'],
            'line_items' => $lineItems,
            'success_url' => $base . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $base . '/checkout/cancel',
            'metadata' => [
                'user_id' => (string) $userId,
                'pending_order_id' => (string) $orderId,
            ],
            'client_reference_id' => 'pending_' . $orderId,
        ]);

        $url = $session->url;
        if ($url === null) {
            throw new \RuntimeException('Stripe did not return a checkout URL.');
        }

        return $url;
    }

    /**
     * Pull session after redirect: normalizes metadata to string map for CheckoutService amount and user checks.
     *
     * @return array{payment_status:string, amount_total:int, metadata:array<string,string>}
     */
    public static function retrieveSession(string $sessionId): array
    {
        $secret = self::secretKey();
        if ($secret === null) {
            throw new \RuntimeException('Stripe is not configured.');
        }

        Stripe::setApiKey($secret);
        $s = Session::retrieve($sessionId);
        $arr = $s->toArray();
        $meta = [];
        if (!empty($arr['metadata']) && is_array($arr['metadata'])) {
            foreach ($arr['metadata'] as $k => $v) {
                $meta[(string) $k] = (string) $v;
            }
        }

        return [
            'payment_status' => (string) ($arr['payment_status'] ?? ''),
            'amount_total' => (int) ($arr['amount_total'] ?? 0),
            'metadata' => $meta,
        ];
    }
}
