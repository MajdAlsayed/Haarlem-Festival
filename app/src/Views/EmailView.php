<?php

declare(strict_types=1);

namespace App\Views;

final class EmailView
{
    /**
     * @param array{status?:string,total_amount?:string,paid_at?:string} $order
     * @param list<array{name:string,quantity:int,unit_price:string,line_total:string}> $lines
     * @param list<array{ticket_code:string,item_name:string}> $tickets
     */
    public static function paidOrder(string $site, int $orderId, array $order, array $lines, array $tickets): string
    {
        return self::render('paid-order.php', compact('site', 'orderId', 'order', 'lines', 'tickets'));
    }

    /**
     * @param array{total_amount?:string,expires_at?:string} $order
     * @param list<array{name:string,quantity:int,unit_price:string,line_total:string}> $lines
     */
    public static function pendingReservation(string $site, int $orderId, array $order, array $lines): string
    {
        return self::render('pending-reservation.php', compact('site', 'orderId', 'order', 'lines'));
    }

    public static function pendingReminder(string $site, int $orderId, string $totalAmount, string $expiresAt): string
    {
        return self::render('pending-reminder.php', compact('site', 'orderId', 'totalAmount', 'expiresAt'));
    }

    private static function render(string $template, array $vars): string
    {
        ob_start();
        extract($vars, EXTR_SKIP);
        require __DIR__ . '/Email/' . $template;

        return (string) ob_get_clean();
    }
}
