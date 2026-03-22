<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Repositories\CartRepository;
use App\Services\CartService;

/**
 * Cart JSON API. Mutating POSTs need CSRF (form key cart); send _csrf in JSON body.
 */
final class CartController
{
    private CartService $cartService;

    public function __construct()
    {
        $this->cartService = new CartService(new CartRepository());
    }

    public function get(): void
    {
        $csrf = Csrf::peek('cart') ?? Csrf::token('cart');
        $this->json([
            'success' => true,
            'cart' => $this->cartService->getCurrentCart()->toArray(),
            'csrf' => $csrf,
        ]);
    }

    public function add(): void
    {
        $data = $this->getInputData();
        if (!$this->requireCsrf($data)) {
            return;
        }
        unset($data['_csrf']);

        $ticketDetailsId = (int) ($data['ticket_details_id'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 1);

        if ($ticketDetailsId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'ticket_details_id is required.',
            ], 422);

            return;
        }

        try {
            $cart = $this->cartService->addItem($ticketDetailsId, $quantity);

            $this->json([
                'success' => true,
                'message' => 'Item added to cart.',
                'cart' => $cart->toArray(),
                'csrf' => Csrf::token('cart'),
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function update(): void
    {
        $data = $this->getInputData();
        if (!$this->requireCsrf($data)) {
            return;
        }
        unset($data['_csrf']);

        $cartItemId = (int) ($data['cart_item_id'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 0);

        if ($cartItemId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'cart_item_id is required.',
            ], 422);

            return;
        }

        $cart = $this->cartService->updateItem($cartItemId, $quantity);

        $this->json([
            'success' => true,
            'message' => 'Cart updated.',
            'cart' => $cart->toArray(),
            'csrf' => Csrf::token('cart'),
        ]);
    }

    public function remove(): void
    {
        $data = $this->getInputData();
        if (!$this->requireCsrf($data)) {
            return;
        }
        unset($data['_csrf']);

        $cartItemId = (int) ($data['cart_item_id'] ?? 0);

        if ($cartItemId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'cart_item_id is required.',
            ], 422);

            return;
        }

        $cart = $this->cartService->removeItem($cartItemId);

        $this->json([
            'success' => true,
            'message' => 'Item removed.',
            'cart' => $cart->toArray(),
            'csrf' => Csrf::token('cart'),
        ]);
    }

    /** @param array<string, mixed> $data */
    private function requireCsrf(array $data): bool
    {
        $token = isset($data['_csrf']) && is_string($data['_csrf']) ? $data['_csrf'] : null;
        if (Csrf::validate('cart', $token)) {
            return true;
        }

        $this->json([
            'success' => false,
            'message' => 'Invalid or expired security token.',
            'csrf' => Csrf::token('cart'),
        ], 403);

        return false;
    }

    private function getInputData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw ?: '{}', true);

            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
