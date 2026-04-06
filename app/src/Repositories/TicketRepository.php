<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\SecureToken;
use PDO;

/**
 * Ticket rows: issued at checkout and used by the door scanner (lookup and scan state).
 */
final class TicketRepository
{
    /**
     * Insert a ticket with a new secure code. Returns ticket_id.
     * Call from checkout when order_items exist.
     */
    public function createForOrderItem(int $orderItemId): int
    {
        $db = Database::getConnection();
        $code = $this->uniqueTicketCode($db);
        $stmt = $db->prepare(
            'INSERT INTO tickets (order_item_id, ticket_code, status) VALUES (:oid, :code, \'valid\')'
        );
        $stmt->execute(['oid' => $orderItemId, 'code' => $code]);

        return (int) $db->lastInsertId();
    }

    public function findByCode(string $ticketCode): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT ticket_id, order_item_id, ticket_code, status, issued_at FROM tickets WHERE ticket_code = :code LIMIT 1'
        );
        $stmt->execute(['code' => $ticketCode]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Ticket + catalog + event for staff scanner UI.
     *
     * @return array<string, mixed>|null
     */
    public function findByCodeWithDetails(string $ticketCode): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT t.ticket_id, t.order_item_id, t.ticket_code, t.status, t.issued_at,
                    td.name AS ticket_name, td.ticket_type,
                    e.title AS event_title, e.event_day
             FROM tickets t
             INNER JOIN order_items oi ON oi.order_item_id = t.order_item_id
             INNER JOIN orders o ON o.order_id = oi.order_id AND o.status = \'paid\'
             INNER JOIN ticket_details td ON td.ticket_details_id = oi.ticket_details_id
             LEFT JOIN events e ON e.event_id = td.event_id
             WHERE t.ticket_code = :code
             LIMIT 1'
        );
        $stmt->execute(['code' => $ticketCode]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /** Tickets issued for paid orders (excludes cancelled ticket rows). */
    public function countSoldForTicketDetails(int $ticketDetailsId): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT COUNT(*)
             FROM tickets t
             INNER JOIN order_items oi ON oi.order_item_id = t.order_item_id
             INNER JOIN orders o ON o.order_id = oi.order_id
             WHERE oi.ticket_details_id = :tdid
               AND o.status = \'paid\'
               AND t.status != \'cancelled\''
        );
        $stmt->execute(['tdid' => $ticketDetailsId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return 'success'|'already_scanned'|'cancelled'|'not_found'
     */
    public function markScannedIfValid(int $ticketId): string
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT status FROM tickets WHERE ticket_id = :id LIMIT 1');
        $stmt->execute(['id' => $ticketId]);
        $status = $stmt->fetchColumn();
        if ($status === false) {
            return 'not_found';
        }
        if ($status === 'scanned') {
            return 'already_scanned';
        }
        if ($status === 'cancelled') {
            return 'cancelled';
        }

        // WHERE status = 'valid' makes the update atomic: two scanners at once → one row updated, other sees already_scanned.
        $upd = $db->prepare(
            "UPDATE tickets SET status = 'scanned', scanned_at = NOW() WHERE ticket_id = :id AND status = 'valid'"
        );
        $upd->execute(['id' => $ticketId]);

        return $upd->rowCount() > 0 ? 'success' : 'already_scanned';
    }

    private function uniqueTicketCode(PDO $db): string
    {
        for ($i = 0; $i < 10; $i++) {
            $code = SecureToken::ticketCode();
            $check = $db->prepare('SELECT 1 FROM tickets WHERE ticket_code = :c LIMIT 1');
            $check->execute(['c' => $code]);
            if (!$check->fetchColumn()) {
                return $code;
            }
        }

        throw new \RuntimeException('Could not generate unique ticket code.');
    }
}
