<?php

declare(strict_types=1);

namespace App\Views;

final class EmailView
{

    public static function paidOrder(string $site, int $orderId, array $order, array $lines, array $tickets): string
    {
        return self::render('paid-order.php', compact('site', 'orderId', 'order', 'lines', 'tickets'));
    }

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
