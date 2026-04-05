<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class TicketDetailsRepository
{
    /** @return ?array<string,mixed> */
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT ticket_details_id, event_id, session_id, ticket_type, category, pass_day, pass_time,
                    schedule_display, sort_order, is_free, name, description, price
             FROM ticket_details WHERE ticket_details_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        return $r ?: null;
    }

    /** @return list<array<string,mixed>> */
    public function listAllForAdmin(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            'SELECT td.ticket_details_id, td.event_id, td.ticket_type, td.category, td.pass_day, td.pass_time,
                    td.schedule_display, td.sort_order, td.is_free, td.name, td.description, td.price,
                    e.title AS event_title
             FROM ticket_details td
             LEFT JOIN events e ON e.event_id = td.event_id
             ORDER BY td.ticket_type ASC, td.category ASC, td.sort_order ASC, td.ticket_details_id ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(array $row): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO ticket_details (event_id, session_id, ticket_type, category, pass_day, pass_time,
             schedule_display, sort_order, is_free, name, description, price)
             VALUES (:eid, :sid, :tt, :cat, :pd, :pt, :sd, :so, :free, :name, :desc, :price)'
        );
        $stmt->execute([
            'eid' => $row['event_id'],
            'sid' => $row['session_id'],
            'tt' => $row['ticket_type'],
            'cat' => $row['category'] ?? 'all',
            'pd' => $row['pass_day'] ?? null,
            'pt' => $row['pass_time'] ?? null,
            'sd' => $row['schedule_display'] ?? null,
            'so' => (int) ($row['sort_order'] ?? 0),
            'free' => !empty($row['is_free']) ? 1 : 0,
            'name' => $row['name'],
            'desc' => $row['description'] ?? null,
            'price' => $row['price'],
        ]);

        return (int) $db->lastInsertId();
    }

    public function update(int $id, array $row): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE ticket_details SET event_id = :eid, session_id = :sid, ticket_type = :tt, category = :cat,
             pass_day = :pd, pass_time = :pt, schedule_display = :sd, sort_order = :so, is_free = :free,
             name = :name, description = :desc, price = :price
             WHERE ticket_details_id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'eid' => $row['event_id'],
            'sid' => $row['session_id'],
            'tt' => $row['ticket_type'],
            'cat' => $row['category'] ?? 'all',
            'pd' => $row['pass_day'] ?? null,
            'pt' => $row['pass_time'] ?? null,
            'sd' => $row['schedule_display'] ?? null,
            'so' => (int) ($row['sort_order'] ?? 0),
            'free' => !empty($row['is_free']) ? 1 : 0,
            'name' => $row['name'],
            'desc' => $row['description'] ?? null,
            'price' => $row['price'],
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM ticket_details WHERE ticket_details_id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * Events that can have an event_ticket (for admin dropdown).
     *
     * @return list<array{event_id:int,title:string,cat:string}>
     */
    public function listEventsForTicketForm(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT e.event_id, e.title, LOWER(et.name) AS cat
             FROM events e
             JOIN event_types et ON et.event_type_id = e.event_type_id
             WHERE LOWER(et.name) IN ('jazz','dance','history','stories')
             ORDER BY et.name ASC, e.event_day ASC, e.start_time ASC, e.title ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return list<array{event_id:int,title:string}> */
    public function listEventsWithoutTicket(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT e.event_id, e.title FROM events e
             JOIN event_types et ON et.event_type_id = e.event_type_id
             LEFT JOIN ticket_details td ON td.event_id = e.event_id
             WHERE td.ticket_details_id IS NULL
               AND LOWER(et.name) IN ('jazz','dance','history','stories')
             ORDER BY e.title ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
