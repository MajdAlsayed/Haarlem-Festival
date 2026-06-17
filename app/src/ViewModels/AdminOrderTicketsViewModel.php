<?php

namespace App\ViewModels;

class AdminOrderTicketsViewModel
{
    public int $orderId;
    public string $siteName;
    public string $pageTitle;
    public string $status;
    public string $totalLabel;

    public array $ticketRows = [];

    public function __construct(array $order, array $tickets, array $app)
    {
        $this->orderId = 0;
        if (isset($order['order_id'])) {
            $this->orderId = (int) $order['order_id'];
        }

        $this->siteName = $this->text($app, 'site_name', 'Haarlem Festival');
        $this->pageTitle = 'Order #' . $this->orderId . ' tickets — ' . $this->siteName;
        $this->status = $this->text($order, 'status', '');

        $total = 0;
        if (isset($order['total_amount'])) {
            $total = $order['total_amount'];
        }
        $this->totalLabel = $this->money($total);

        foreach ($tickets as $ticket) {
            $this->ticketRows[] = [
                'itemName' => (string) $ticket['item_name'],
                'code' => (string) $ticket['ticket_code'],
            ];
        }
    }

    private function text(array $row, string $key, string $fallback): string
    {
        if (isset($row[$key]) && (string) $row[$key] !== '') {
            return (string) $row[$key];
        }

        return $fallback;
    }

    private function money(mixed $value): string
    {
        $amount = 0.0;
        if (is_numeric($value)) {
            $amount = (float) $value;
        }

        return '€ ' . number_format($amount, 2, ',', '.');
    }
}
