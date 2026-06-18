<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;
use PDO;

final class OrderRepository extends Repository
{

    // admin list + csv source
    public function getAllForExport(): array
    {
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
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findOrderDetailForAdmin(int $orderId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                o.order_id,
                o.user_id,
                o.status,
                o.total_amount,
                o.created_at,
                o.paid_at,
                o.expires_at,
                o.payment_reminder_sent,
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
             WHERE o.order_id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function findOrderById(int $orderId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT order_id, user_id, status, total_amount, paid_at, created_at, expires_at, payment_reminder_sent
             FROM orders WHERE order_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function listOrdersForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT order_id, status, total_amount, paid_at, created_at, expires_at
             FROM orders
             WHERE user_id = :uid
             ORDER BY created_at DESC'
        );
        $stmt->execute(['uid' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderLineItemsForInvoice(int $orderId): array
    {
        $stmt = $this->db->prepare(
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

    public function createPaidOrder(int $userId, float $totalAmount, ?string $stripeCheckoutSessionId = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO orders (user_id, status, total_amount, paid_at, stripe_checkout_session_id)
             VALUES (:user_id, \'paid\', :total, NOW(), :sid)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'total' => number_format($totalAmount, 2, '.', ''),
            'sid' => $stripeCheckoutSessionId,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function createPendingOrder(int $userId, float $totalAmount): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO orders (user_id, status, total_amount, paid_at, expires_at, payment_reminder_sent, stripe_checkout_session_id)
             VALUES (:user_id, \'pending\', :total, NULL, DATE_ADD(NOW(), INTERVAL 24 HOUR), 0, NULL)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'total' => number_format($totalAmount, 2, '.', ''),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function expireStalePendingOrders(): int
    {
        $stmt = $this->db->exec(
            "UPDATE orders SET status = 'canceled'
             WHERE status = 'pending'
               AND expires_at IS NOT NULL
               AND expires_at < NOW()"
        );

        return $stmt !== false ? (int) $stmt : 0;
    }

    public function listPendingOrdersForReminder(): array
    {
        $stmt = $this->db->query(
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
        $stmt = $this->db->prepare(
            'UPDATE orders SET payment_reminder_sent = 1 WHERE order_id = :id AND status = \'pending\''
        );
        $stmt->execute(['id' => $orderId]);
    }

    public function getOrderFulfillmentLines(int $orderId): array
    {
        $stmt = $this->db->prepare(
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

    public function lockPendingOrderForPay(int $orderId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
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
        if ($stripeCheckoutSessionId !== null && $stripeCheckoutSessionId !== '') {
            $stmt = $this->db->prepare(
                'UPDATE orders
                 SET status = \'paid\', paid_at = NOW(), expires_at = NULL, stripe_checkout_session_id = :sid
                 WHERE order_id = :id AND status = \'pending\''
            );
            $stmt->execute(['id' => $orderId, 'sid' => $stripeCheckoutSessionId]);
        } else {
            $stmt = $this->db->prepare(
                'UPDATE orders
                 SET status = \'paid\', paid_at = NOW(), expires_at = NULL
                 WHERE order_id = :id AND status = \'pending\''
            );
            $stmt->execute(['id' => $orderId]);
        }

        // rowCount 0 = someone else already flipped it to paid, or it was never pending.
        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('This order is not pending anymore. Refresh your orders list.');
        }
    }

    public function findOrderIdByStripeSessionId(string $stripeSessionId): ?int
    {
        $stmt = $this->db->prepare(
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
        $stmt = $this->db->prepare(
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

        return (int) $this->db->lastInsertId();
    }

    // account pages — order must belong to user
    public function findForCustomer(int $orderId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT order_id, status, total_amount, paid_at, created_at, stripe_checkout_session_id, expires_at, payment_reminder_sent
             FROM orders
             WHERE order_id = :id AND user_id = :uid
             LIMIT 1'
        );
        $stmt->execute(['id' => $orderId, 'uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getTicketsWithDetailsForOrder(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.ticket_code,
                    td.name AS item_name, td.ticket_type,
                    e.title AS event_title, e.event_day, e.start_time
             FROM tickets t
             INNER JOIN order_items oi ON oi.order_item_id = t.order_item_id
             INNER JOIN ticket_details td ON td.ticket_details_id = oi.ticket_details_id
             LEFT JOIN events e ON e.event_id = td.event_id
             WHERE oi.order_id = :oid
             ORDER BY t.ticket_id ASC'
        );
        $stmt->execute(['oid' => $orderId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'ticket_code' => (string) $r['ticket_code'],
                'item_name' => (string) $r['item_name'],
                'ticket_type' => (string) ($r['ticket_type'] ?? ''),
                'event_title' => $r['event_title'] !== null ? (string) $r['event_title'] : null,
                'event_day' => $r['event_day'] !== null ? (string) $r['event_day'] : null,
                'start_time' => $r['start_time'] !== null ? (string) $r['start_time'] : null,
            ];
        }

        return $out;
    }

    public function getTicketCodesForOrder(int $orderId): array
    {
        $stmt = $this->db->prepare(
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
