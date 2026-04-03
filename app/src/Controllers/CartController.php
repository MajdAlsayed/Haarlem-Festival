<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\TicketDetailsRepository;

final class CartController
{
    /** @return list<array{ticket_details_id:int,qty:int}> */
    private function items(): array
    {
        $c = $_SESSION['cart'] ?? [];
        if (!is_array($c)) {
            return [];
        }

        return $c;
    }

    private function saveItems(array $items): void
    {
        $_SESSION['cart'] = array_values($items);
    }

    public function get(): void
    {
        $repo = new TicketDetailsRepository();
        $lines = [];
        $total = 0.0;
        foreach ($this->items() as $row) {
            $id = (int) ($row['ticket_details_id'] ?? 0);
            $qty = max(1, (int) ($row['qty'] ?? 1));
            $td = $repo->findById($id);
            if (!$td) {
                continue;
            }
            $price = (float) $td['price'];
            $line = $price * $qty;
            if (!empty($td['is_free'])) {
                $price = 0.0;
                $line = 0.0;
            }
            $total += $line;
            $lines[] = [
                'ticket_details_id' => $id,
                'qty' => $qty,
                'name' => (string) $td['name'],
                'unit' => $price,
                'line' => $line,
                'is_free' => (bool) (int) $td['is_free'],
            ];
        }

        $app = (new \App\Repositories\SettingsRepository())->getAll();
        $success = Session::getFlash('cart_success');
        require __DIR__ . '/../Views/Cart/index.php';
    }

    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tickets');
            exit;
        }
        $id = (int) ($_POST['ticket_details_id'] ?? 0);
        $qty = max(1, (int) ($_POST['qty'] ?? 1));
        $return = trim((string) ($_POST['return'] ?? '/tickets'));
        if ($return === '' || !str_starts_with($return, '/')) {
            $return = '/tickets';
        }

        $repo = new TicketDetailsRepository();
        if ($repo->findById($id) === null) {
            Session::setFlash('cart_error', 'Ticket not found.');
            header('Location: ' . $return);
            exit;
        }

        $cart = $this->items();
        $found = false;
        foreach ($cart as $i => $row) {
            if ((int) ($row['ticket_details_id'] ?? 0) === $id) {
                $cart[$i]['qty'] = max(1, (int) ($row['qty'] ?? 1) + $qty);
                $found = true;
                break;
            }
        }
        if (!$found) {
            $cart[] = ['ticket_details_id' => $id, 'qty' => $qty];
        }
        $this->saveItems($cart);

        Session::setFlash('cart_success', 'Added to your cart.');
        header('Location: ' . $return);
        exit;
    }

    public function remove(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cart');
            exit;
        }
        $id = (int) ($_POST['ticket_details_id'] ?? 0);
        $cart = array_values(array_filter($this->items(), fn ($r) => (int) ($r['ticket_details_id'] ?? 0) !== $id));
        $this->saveItems($cart);
        header('Location: /cart');
        exit;
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cart');
            exit;
        }
        $id = (int) ($_POST['ticket_details_id'] ?? 0);
        $qty = max(0, (int) ($_POST['qty'] ?? 0));
        $cart = $this->items();
        foreach ($cart as $i => $row) {
            if ((int) ($row['ticket_details_id'] ?? 0) === $id) {
                if ($qty < 1) {
                    unset($cart[$i]);
                } else {
                    $cart[$i]['qty'] = $qty;
                }
                break;
            }
        }
        $this->saveItems(array_values($cart));
        header('Location: /cart');
        exit;
    }
}
