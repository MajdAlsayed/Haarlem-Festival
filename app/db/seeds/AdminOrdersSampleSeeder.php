<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Optional sample order + line item for admin / testing (only if `orders` is empty).
 * Requires: migrations + EventSeeder (or any row in `events`).
 *
 * Run: docker compose run --rm php vendor/bin/phinx seed:run -s AdminOrdersSampleSeeder
 */
final class AdminOrdersSampleSeeder extends AbstractSeed
{
    public function run(): void
    {
        foreach (['orders', 'order_items', 'ticket_details', 'events'] as $table) {
            if (!$this->hasTable($table)) {
                echo "[SKIP] AdminOrdersSampleSeeder: table `{$table}` missing — run migrations first\n";

                return;
            }
        }

        $row = $this->fetchRow('SELECT COUNT(*) AS c FROM orders');
        $count = (int) ($row['c'] ?? 0);
        if ($count > 0) {
            echo "[SKIP] AdminOrdersSampleSeeder: `orders` already has rows\n";

            return;
        }

        $event = $this->fetchRow('SELECT event_id FROM events ORDER BY event_id ASC LIMIT 1');
        if ($event === null || !isset($event['event_id'])) {
            echo "[SKIP] AdminOrdersSampleSeeder: no events — run EventSeeder first\n";

            return;
        }

        $eventId = (int) $event['event_id'];

        $td = $this->fetchRow(
            "SELECT ticket_details_id FROM ticket_details WHERE event_id = {$eventId} LIMIT 1"
        );
        $ticketDetailsId = $td !== null ? (int) ($td['ticket_details_id'] ?? 0) : 0;

        if ($ticketDetailsId < 1) {
            $this->execute(
                "INSERT INTO ticket_details (event_id, session_id, ticket_type, name, description, price)
                 VALUES ({$eventId}, NULL, 'event_ticket', 'Standard', 'Sample ticket (admin preview)', 15.00)"
            );
            $conn = $this->getAdapter()->getConnection();
            $ticketDetailsId = (int) $conn->lastInsertId();
        }

        $user = $this->fetchRow('SELECT user_id FROM users ORDER BY user_id ASC LIMIT 1');
        $userSql = $user !== null && isset($user['user_id']) ? (string) (int) $user['user_id'] : 'NULL';

        $this->execute(
            "INSERT INTO orders (user_id, status, total_amount) VALUES ({$userSql}, 'paid', 30.00)"
        );
        $conn = $this->getAdapter()->getConnection();
        $orderId = (int) $conn->lastInsertId();

        $this->execute(
            "INSERT INTO order_items (order_id, ticket_details_id, quantity, unit_price, line_total)
             VALUES ({$orderId}, {$ticketDetailsId}, 2, 15.00, 30.00)"
        );

        echo "[DONE] AdminOrdersSampleSeeder: sample order_id={$orderId}, 2× ticket_details_id={$ticketDetailsId}\n";
    }
}
