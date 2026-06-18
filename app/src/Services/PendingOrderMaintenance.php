<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;

// runs on each request — pay-later reminder emails
final class PendingOrderMaintenance
{
    // expire stale pending + send one reminder email each
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
                // Whole site shouldn’t 500 because mail or the log file failed.
            }
        }
    }
}
