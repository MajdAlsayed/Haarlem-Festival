<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;

/**
 * Shopping cart API (JSON). All mutating POST requests require CSRF token (form key: cart).
 */
final class CartController
{
    public function get(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        $items = $_SESSION['cart'] ?? [];
        $csrf = Csrf::peek('cart') ?? Csrf::token('cart');
        echo json_encode([
            'ok' => true,
            'items' => array_values($items),
            'total' => $this->computeTotal($items),
            'csrf' => $csrf,
        ]);
    }

    public function add(): void
    {
        $this->mutate(function (): void {
            $ticketDetailsId = (int) ($_POST['ticket_details_id'] ?? 0);
            $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
            if ($ticketDetailsId <= 0) {
                return;
            }
            $_SESSION['cart'] ??= [];
            $key = 'td_' . $ticketDetailsId;
            $_SESSION['cart'][$key] = [
                'ticket_details_id' => $ticketDetailsId,
                'quantity' => (int) ($_SESSION['cart'][$key]['quantity'] ?? 0) + $quantity,
            ];
        });
    }

    public function remove(): void
    {
        $this->mutate(function (): void {
            $key = trim((string) ($_POST['key'] ?? ''));
            if ($key !== '' && isset($_SESSION['cart'][$key])) {
                unset($_SESSION['cart'][$key]);
            }
        });
    }

    public function update(): void
    {
        $this->mutate(function (): void {
            $key = trim((string) ($_POST['key'] ?? ''));
            $quantity = (int) ($_POST['quantity'] ?? 0);
            if ($key === '' || !isset($_SESSION['cart'][$key])) {
                return;
            }
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$key]);
            } else {
                $_SESSION['cart'][$key]['quantity'] = $quantity;
            }
        });
    }

    private function mutate(callable $fn): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');

        if (!Csrf::validate('cart', $_POST['_csrf'] ?? null)) {
            http_response_code(403);
            echo json_encode([
                'ok' => false,
                'error' => 'Invalid or expired security token.',
                'csrf' => Csrf::token('cart'),
            ]);

            return;
        }

        $fn();

        $items = $_SESSION['cart'] ?? [];
        echo json_encode([
            'ok' => true,
            'items' => array_values($items),
            'total' => $this->computeTotal($items),
            'csrf' => Csrf::token('cart'),
        ]);
    }

    /** @param array<string, array{ticket_details_id: int, quantity: int}> $items */
    private function computeTotal(array $items): string
    {
        // Placeholder until prices are loaded from DB; keeps API stable for frontend.
        $sum = 0.0;
        foreach ($items as $row) {
            $sum += (float) ($row['quantity'] ?? 0) * 10.0;
        }

        return number_format($sum, 2, '.', '');
    }
}
