<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\SecureToken;
use PDO;

/**
 * Ticket rows (scanner / QR). Uses cryptographically secure ticket_code values.
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
