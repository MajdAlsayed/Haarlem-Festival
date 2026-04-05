<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Orders for admin export (joins customer when user_id is set).
 */
final class OrderRepository
{
    /**
     * All orders with customer fields and line item count for export.
     *
     * @return list<array<string, mixed>>
     */
    public function getAllForExport(): array
    {
        $db = Database::getConnection();
        $sql = '
            SELECT
                o.order_id,
                o.user_id,
                o.status,
                o.total_amount,
                o.created_at,
                o.paid_at,
                u.email AS customer_email,
                u.first_name AS customer_first_name,
                u.last_name AS customer_last_name,
                (
                    SELECT COUNT(*)
                    FROM order_items oi
                    WHERE oi.order_id = o.order_id
                ) AS line_items_count
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            ORDER BY o.created_at DESC
        ';
        $stmt = $db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return ?array{order_id:int,user_id:?int,status:string,total_amount:string,paid_at:?string,created_at:string}
     */
    public function findOrderById(int $orderId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT order_id, user_id, status, total_amount, paid_at, created_at, expires_at, payment_reminder_sent
             FROM orders WHERE order_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * @return list<array{order_id:int,status:string,total_amount:string,paid_at:?string,created_at:string}>
     */
    public function listOrdersForUser(int $userId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT order_id, status, total_amount, paid_at, created_at, expires_at
             FROM orders
             WHERE user_id = :uid
             ORDER BY created_at DESC'
        );
        $stmt->execute(['uid' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Line items for invoice / confirmation (any order id; caller must enforce access).
     *
     * @return list<array{name:string,quantity:int,unit_price:string,line_total:string}>
     */
    public function getOrderLineItemsForInvoice(int $orderId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT td.name, oi.quantity, oi.unit_price, oi.line_total
             FROM order_items oi
             INNER JOIN ticket_details td ON td.ticket_details_id = oi.ticket_details_id
             WHERE oi.order_id = :oid
             ORDER BY oi.order_item_id ASC'
        );
        $stmt->execute(['oid' => $orderId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'name' => (string) $r['name'],
                'quantity' => (int) $r['quantity'],
                'unit_price' => (string) $r['unit_price'],
                'line_total' => (string) $r['line_total'],
            ];
        }

        return $out;
    }

    /**
     * Paid order. Optional Stripe Checkout session id (real payments) or null (demo / local).
     */
    public function createPaidOrder(int $userId, float $totalAmount, ?string $stripeCheckoutSessionId = null): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO orders (user_id, status, total_amount, paid_at, stripe_checkout_session_id)
             VALUES (:user_id, \'paid\', :total, NOW(), :sid)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'total' => number_format($totalAmount, 2, '.', ''),
            'sid' => $stripeCheckoutSessionId,
        ]);

        return (int) $db->lastInsertId();
    }

    public function createPendingOrder(int $userId, float $totalAmount): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO orders (user_id, status, total_amount, paid_at, expires_at, payment_reminder_sent, stripe_checkout_session_id)
             VALUES (:user_id, \'pending\', :total, NULL, DATE_ADD(NOW(), INTERVAL 24 HOUR), 0, NULL)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'total' => number_format($totalAmount, 2, '.', ''),
        ]);

        return (int) $db->lastInsertId();
    }

    /** @return int Number of orders expired */
    public function expireStalePendingOrders(): int
    {
        $db = Database::getConnection();
        $stmt = $db->exec(
            "UPDATE orders SET status = 'canceled'
             WHERE status = 'pending'
               AND expires_at IS NOT NULL
               AND expires_at < NOW()"
        );

        return $stmt !== false ? (int) $stmt : 0;
    }

    /**
     * Pending orders that should receive one reminder (created ≥12h ago, not expired, flag unset).
     *
     * @return list<array{order_id:int,user_id:int,total_amount:string,expires_at:string}>
     */
    public function listPendingOrdersForReminder(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT order_id, user_id, total_amount, expires_at
             FROM orders
             WHERE status = 'pending'
               AND expires_at IS NOT NULL
               AND expires_at > NOW()
               AND payment_reminder_sent = 0
               AND created_at <= DATE_SUB(NOW(), INTERVAL 12 HOUR)"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'order_id' => (int) $r['order_id'],
                'user_id' => (int) $r['user_id'],
                'total_amount' => (string) $r['total_amount'],
                'expires_at' => (string) $r['expires_at'],
            ];
        }

        return $out;
    }

    public function markPaymentReminderSent(int $orderId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE orders SET payment_reminder_sent = 1 WHERE order_id = :id AND status = \'pending\''
        );
        $stmt->execute(['id' => $orderId]);
    }

    /**
     * @return list<array{order_item_id:int,ticket_details_id:int,quantity:int,unit_price:float,line_total:float}>
     */
    public function getOrderFulfillmentLines(int $orderId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT order_item_id, ticket_details_id, quantity, unit_price, line_total
             FROM order_items
             WHERE order_id = :oid
             ORDER BY order_item_id ASC'
        );
        $stmt->execute(['oid' => $orderId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'order_item_id' => (int) $r['order_item_id'],
                'ticket_details_id' => (int) $r['ticket_details_id'],
                'quantity' => (int) $r['quantity'],
                'unit_price' => (float) $r['unit_price'],
                'line_total' => (float) $r['line_total'],
            ];
        }

        return $out;
    }

    /**
     * Call inside an open transaction (InnoDB) before issuing tickets for pay-later completion.
     *
     * @return ?array<string, mixed>
     */
    public function lockPendingOrderForPay(int $orderId, int $userId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT order_id, user_id, status, total_amount, expires_at
             FROM orders
             WHERE order_id = :id AND user_id = :uid AND status = \'pending\'
               AND expires_at IS NOT NULL
               AND expires_at > NOW()
             FOR UPDATE'
        );
        $stmt->execute(['id' => $orderId, 'uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function markOrderPaidAndClearPendingWindow(int $orderId, ?string $stripeCheckoutSessionId = null): void
    {
        $db = Database::getConnection();
        if ($stripeCheckoutSessionId !== null && $stripeCheckoutSessionId !== '') {
            $stmt = $db->prepare(
                'UPDATE orders
                 SET status = \'paid\', paid_at = NOW(), expires_at = NULL, stripe_checkout_session_id = :sid
                 WHERE order_id = :id AND status = \'pending\''
            );
            $stmt->execute(['id' => $orderId, 'sid' => $stripeCheckoutSessionId]);
        } else {
            $stmt = $db->prepare(
                'UPDATE orders
                 SET status = \'paid\', paid_at = NOW(), expires_at = NULL
                 WHERE order_id = :id AND status = \'pending\''
            );
            $stmt->execute(['id' => $orderId]);
        }

        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('This order is not pending anymore. Refresh your orders list.');
        }
    }

    public function findOrderIdByStripeSessionId(string $stripeSessionId): ?int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT order_id FROM orders WHERE stripe_checkout_session_id = :sid LIMIT 1'
        );
        $stmt->execute(['sid' => $stripeSessionId]);
        $v = $stmt->fetchColumn();

        return $v !== false ? (int) $v : null;
    }

    public function insertOrderItem(
        int $orderId,
        int $ticketDetailsId,
        int $quantity,
        float $unitPrice,
        float $lineTotal
    ): int {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO order_items (order_id, ticket_details_id, quantity, unit_price, line_total)
             VALUES (:oid, :tdid, :qty, :unit, :line)'
        );
        $stmt->execute([
            'oid' => $orderId,
            'tdid' => $ticketDetailsId,
            'qty' => $quantity,
            'unit' => number_format($unitPrice, 2, '.', ''),
            'line' => number_format($lineTotal, 2, '.', ''),
        ]);

        return (int) $db->lastInsertId();
    }

    /**
     * @return ?array{order_id:int,status:string,total_amount:string,paid_at:?string,created_at:string}
     */
    public function findForCustomer(int $orderId, int $userId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT order_id, status, total_amount, paid_at, created_at, stripe_checkout_session_id, expires_at, payment_reminder_sent
             FROM orders
             WHERE order_id = :id AND user_id = :uid
             LIMIT 1'
        );
        $stmt->execute(['id' => $orderId, 'uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Ticket codes issued for this order (for confirmation page).
     *
     * @return list<array{ticket_code:string, item_name:string}>
     */
    public function getTicketCodesForOrder(int $orderId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT t.ticket_code, td.name AS item_name
             FROM tickets t
             INNER JOIN order_items oi ON oi.order_item_id = t.order_item_id
             INNER JOIN ticket_details td ON td.ticket_details_id = oi.ticket_details_id
             WHERE oi.order_id = :oid
             ORDER BY t.ticket_id ASC'
        );
        $stmt->execute(['oid' => $orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
