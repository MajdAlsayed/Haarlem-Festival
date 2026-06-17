<?php

namespace App\ViewModels;

class AdminOrderDetailViewModel
{
    public int $orderId;
    public string $siteName;
    public string $pageTitle;

    public string $customerName;
    public string $customerEmail;
    public string $userId;

    public string $status;
    public string $statusClass;
    public string $totalLabel;
    public string $paidAt;
    public string $createdAt;
    public string $expiresAt;

    public array $lineRows = [];

    public array $ticketRows = [];

    public function __construct(array $order, array $lines, array $tickets, array $app)
    {
        $this->orderId = 0;
        if (isset($order['order_id'])) {
            $this->orderId = (int) $order['order_id'];
        }

        $this->siteName = $this->text($app, 'site_name', 'Haarlem Festival');
        $this->pageTitle = 'Order #' . $this->orderId . ' — ' . $this->siteName;

        $name = trim($this->text($order, 'customer_first_name', '') . ' ' . $this->text($order, 'customer_last_name', ''));
        if ($name === '') {
            $name = '—';
        }
        $this->customerName = $name;
        $this->customerEmail = $this->text($order, 'customer_email', '—');
        $this->userId = $this->text($order, 'user_id', '—');

        $this->status = $this->text($order, 'status', '');
        $this->statusClass = $this->statusClass($this->status);

        $total = 0;
        if (isset($order['total_amount'])) {
            $total = $order['total_amount'];
        }
        $this->totalLabel = $this->money($total);
        $this->paidAt = $this->text($order, 'paid_at', '—');
        $this->createdAt = $this->text($order, 'created_at', '—');
        $this->expiresAt = $this->text($order, 'expires_at', '');

        foreach ($lines as $line) {
            $this->lineRows[] = [
                'name' => (string) $line['name'],
                'quantity' => (string) $line['quantity'],
                'unitPrice' => $this->money($line['unit_price']),
                'lineTotal' => $this->money($line['line_total']),
            ];
        }

        foreach ($tickets as $ticket) {
            $this->ticketRows[] = [
                'itemName' => (string) $ticket['item_name'],
                'code' => (string) $ticket['ticket_code'],
            ];
        }
    }

    // read a field, or the fallback when it's missing/empty
    private function text(array $row, string $key, string $fallback): string
    {
        if (isset($row[$key]) && (string) $row[$key] !== '') {
            return (string) $row[$key];
        }

        return $fallback;
    }

    // money label, e.g. "€ 17,50"
    private function money(mixed $value): string
    {
        $amount = 0.0;
        if (is_numeric($value)) {
            $amount = (float) $value;
        }

        return '€ ' . number_format($amount, 2, ',', '.');
    }

    // map an order status to its badge class
    private function statusClass(string $status): string
    {
        return match ($status) {
            'paid' => 'admin-badge admin-badge-paid',
            'pending' => 'admin-badge admin-badge-pending',
            'canceled', 'cancelled' => 'admin-badge admin-badge-canceled',
            default => 'admin-badge admin-badge-inactive',
        };
    }
}
