<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class PersonalProgramRepository
{
    public function addItem(int $userId, int $ticketDetailsId): void
    {
        if ($userId <= 0 || $ticketDetailsId <= 0) {
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT IGNORE INTO personal_program_items (user_id, ticket_details_id)
             VALUES (:uid, :tdid)'
        );
        $stmt->execute(['uid' => $userId, 'tdid' => $ticketDetailsId]);
    }

    /**
     * @return list<array{name:string,event_title:?string,event_day:?string,start_time:?string,ticket_details_id:int}>
     */
    public function listForUser(int $userId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT td.ticket_details_id, td.name, e.title AS event_title, e.event_day, e.start_time
             FROM personal_program_items p
             INNER JOIN ticket_details td ON td.ticket_details_id = p.ticket_details_id
             LEFT JOIN events e ON e.event_id = td.event_id
             WHERE p.user_id = :uid
             ORDER BY p.created_at DESC'
        );
        $stmt->execute(['uid' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'ticket_details_id' => (int) $r['ticket_details_id'],
                'name' => (string) $r['name'],
                'event_title' => $r['event_title'] !== null ? (string) $r['event_title'] : null,
                'event_day' => $r['event_day'] !== null ? (string) $r['event_day'] : null,
                'start_time' => $r['start_time'] !== null ? (string) $r['start_time'] : null,
            ];
        }

        return $out;
    }
}
