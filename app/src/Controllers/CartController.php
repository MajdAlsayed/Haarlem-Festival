<?php

namespace App\Controllers;

class CartController
{
    private function ensureCart(): void
    {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = ['items' => []];
        }
        if (!isset($_SESSION['cart']['items']) || !is_array($_SESSION['cart']['items'])) {
            $_SESSION['cart']['items'] = [];
        }
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    private function totals(array $items): array
    {
        $count = 0;
        $total = 0.0;

        foreach ($items as $it) {
            $qty = (int)($it['qty'] ?? 0);
            $price = (float)($it['unit_price'] ?? 0);
            $count += $qty;
            $total += $qty * $price;
        }

        return ['count' => $count, 'total' => round($total, 2)];
    }

    public function get(): void
    {
        $this->ensureCart();
        $itemsAssoc = $_SESSION['cart']['items'];
        $totals = $this->totals($itemsAssoc);

        $this->json([
            'items' => array_values($itemsAssoc),
            'count' => $totals['count'],
            'total' => $totals['total'],
        ]);
    }

    public function add(): void
    {
        $this->ensureCart();

        $id = isset($_POST['ticket_product_id']) ? (int)$_POST['ticket_product_id'] : 0;
        $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

        if ($id <= 0 || $qty <= 0) {
            $this->json(['error' => 'Invalid item or quantity'], 400);
        }

        $title = trim((string)($_POST['title'] ?? 'Ticket'));
        $unitPrice = (float)($_POST['unit_price'] ?? 0);

        if (!isset($_SESSION['cart']['items'][$id])) {
            $_SESSION['cart']['items'][$id] = [
                'id' => $id,
                'title' => $title,
                'unit_price' => $unitPrice,
                'qty' => 0,
            ];
        }

        $_SESSION['cart']['items'][$id]['qty'] += $qty;

        $totals = $this->totals($_SESSION['cart']['items']);
        $this->json([
            'ok' => true,
            'items' => array_values($_SESSION['cart']['items']),
            'count' => $totals['count'],
            'total' => $totals['total'],
        ]);
    }

    public function remove(): void
    {
        $this->ensureCart();

        $id = isset($_POST['ticket_product_id']) ? (int)$_POST['ticket_product_id'] : 0;
        if ($id <= 0) {
            $this->json(['error' => 'Invalid item'], 400);
        }

        unset($_SESSION['cart']['items'][$id]);

        $totals = $this->totals($_SESSION['cart']['items']);
        $this->json([
            'ok' => true,
            'items' => array_values($_SESSION['cart']['items']),
            'count' => $totals['count'],
            'total' => $totals['total'],
        ]);
    }

    public function update(): void
    {
        $this->ensureCart();

        $id = isset($_POST['ticket_product_id']) ? (int)$_POST['ticket_product_id'] : 0;
        $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 0;

        if ($id <= 0) {
            $this->json(['error' => 'Invalid item'], 400);
        }

        if ($qty <= 0) {
            unset($_SESSION['cart']['items'][$id]);
        } else {
            if (!isset($_SESSION['cart']['items'][$id])) {
                $this->json(['error' => 'Item not found'], 404);
            }
            $_SESSION['cart']['items'][$id]['qty'] = $qty;
        }

        $totals = $this->totals($_SESSION['cart']['items']);
        $this->json([
            'ok' => true,
            'items' => array_values($_SESSION['cart']['items']),
            'count' => $totals['count'],
            'total' => $totals['total'],
        ]);
    }
}
