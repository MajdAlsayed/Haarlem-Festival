<?php

declare(strict_types=1);

namespace App\Views;

// festival tickets use the 9% dutch reduced (cultural) vat rate
final class InvoiceView
{
    private const VAT_RATE = 0.09;

    public static function html(array $order, array $lines, string $customerName, string $customerEmail, string $site): string
    {
        $orderId = (int) ($order['order_id'] ?? 0);
        $invoiceNo = 'INV-' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);

        $date = (string) ($order['paid_at'] ?? '');
        if ($date === '') {
            $date = (string) ($order['created_at'] ?? '');
        }

        $total = (float) ($order['total_amount'] ?? 0);
        $net = self::money($total / (1 + self::VAT_RATE));
        $vat = self::money($total - ($total / (1 + self::VAT_RATE)));
        $totalStr = self::money($total);

        ob_start();
        require __DIR__ . '/Invoice/invoice.php';

        return (string) ob_get_clean();
    }

    private static function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
