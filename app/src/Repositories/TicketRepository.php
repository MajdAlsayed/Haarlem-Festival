<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;
use App\Core\SecureToken;
use PDO;
use PDOStatement;
use RuntimeException;

final class TicketRepository extends Repository
{
    private const CODE_GENERATION_ATTEMPTS = 10;

    // one qr row per seat at checkout
    public function createForOrderItem(int $orderItemId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tickets (order_item_id, ticket_code, status) VALUES (:oid, :code, \'valid\')'
        );
        $stmt->execute([
            'oid' => $orderItemId,
            'code' => $this->uniqueTicketCode(),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByCode(string $ticketCode): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ticket_id, order_item_id, ticket_code, status, issued_at FROM tickets WHERE ticket_code = :code LIMIT 1'
        );
        $stmt->execute(['code' => $ticketCode]);

        return $this->fetchRow($stmt);
    }

    // scanner — only paid orders
    public function findByCodeWithDetails(string $ticketCode): ?array
    {
        $stmt = $this->db->prepare(
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

        return $this->fetchRow($stmt);
    }

    public function countSoldForTicketDetails(int $ticketDetailsId): int
    {
        $stmt = $this->db->prepare(
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

    // atomic update so two scanners dont both pass
    public function markScannedIfValid(int $ticketId): string
    {
        $stmt = $this->db->prepare('SELECT status FROM tickets WHERE ticket_id = :id LIMIT 1');
        $stmt->execute(['id' => $ticketId]);
        $status = $stmt->fetchColumn();
        if ($status === false) {
            return 'not_found';
        }

        $status = (string) $status;
        if ($status === 'scanned') {
            return 'already_scanned';
        }
        if ($status === 'cancelled') {
            return 'cancelled';
        }

        $update = $this->db->prepare(
            "UPDATE tickets SET status = 'scanned', scanned_at = NOW() WHERE ticket_id = :id AND status = 'valid'"
        );
        $update->execute(['id' => $ticketId]);

        return $update->rowCount() > 0 ? 'success' : 'already_scanned';
    }

    private function uniqueTicketCode(): string
    {
        for ($attempt = 0; $attempt < self::CODE_GENERATION_ATTEMPTS; $attempt++) {
            $code = SecureToken::ticketCode();
            $check = $this->db->prepare('SELECT 1 FROM tickets WHERE ticket_code = :c LIMIT 1');
            $check->execute(['c' => $code]);
            if ($check->fetchColumn() === false) {
                return $code;
            }
        }

        throw new RuntimeException('Could not generate unique ticket code.');
    }

    private function fetchRow(PDOStatement $stmt): ?array
    {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }
}
