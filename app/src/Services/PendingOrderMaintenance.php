<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;

/** Pay-later housekeeping without a cron job; invoked from the front controller after Session::start(). */
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
                // Whole site shouldn’t 500 because mail or the log file failed.
            }
        }
    }
}
