<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;

/**
 * Expires unpaid reservations and sends reminder emails (best-effort; run on each request).
 */
final class PendingOrderMaintenance
{
    public static function run(): void
    {
        $orders = new OrderRepository();
        $orders->expireStalePendingOrders();

        foreach ($orders->listPendingOrdersForReminder() as $row) {
            try {
                (new OrderConfirmationMailer())->sendPendingPaymentReminder(
                    $row['order_id'],
                    $row['user_id'],
                    $row['total_amount'],
                    $row['expires_at']
                );
                $orders->markPaymentReminderSent($row['order_id']);
            } catch (\Throwable) {
                // Do not block requests
            }
        }
    }
}
