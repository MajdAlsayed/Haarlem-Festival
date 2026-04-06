<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class TicketsRepository
{
    public const CATEGORIES = ['jazz', 'dance', 'history', 'stories'];

    public function getIntroText(): string
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1');
            $stmt->execute(['k' => 'tickets_intro']);
            $v = $stmt->fetchColumn();
            if (is_string($v) && trim($v) !== '') {
                return $v;
            }
        } catch (\Throwable) {
            // fall through
        }

        return 'Explore all events from History tours to Jazz concerts, Dance nights, and Stories performances.';
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getPassesForCategory(string $category): array
    {
        $category = strtolower(trim($category));
        if (!in_array($category, self::CATEGORIES, true)) {
            $category = 'jazz';
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT ticket_details_id, ticket_type, category, pass_day, pass_time, schedule_display,
                    sort_order, is_free, name, description, price
             FROM ticket_details
             WHERE ticket_type IN ('day_pass', 'all_access_pass')
               AND (LOWER(category) = :cat OR LOWER(category) = 'all')
             ORDER BY sort_order ASC, ticket_details_id ASC"
        );
        $stmt->execute(['cat' => $category]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'normalizeRow'], $rows);
    }

    /**
     * @return array<string, list<array<string,mixed>>>
     */
    public function getEventTicketsGroupedByDay(string $category): array
    {
        $category = strtolower(trim($category));
        if (!in_array($category, self::CATEGORIES, true)) {
            $category = 'jazz';
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT td.ticket_details_id, td.name, td.description, td.price, td.is_free,
                    e.event_id, e.event_day,
                    CASE
                        WHEN LOWER(et.name) = 'history' THEN s.start_time
                        ELSE e.start_time
                   END AS start_time,
                   CASE
                       WHEN LOWER(et.name) = 'history' THEN s.end_time
                       ELSE e.end_time
                   END AS end_time,
                    e.hall,v.name AS venue_name
             FROM ticket_details td
             INNER JOIN events e ON e.event_id = td.event_id
             LEFT JOIN sessions s ON s.session_id = td.session_id
             INNER JOIN event_types et ON et.event_type_id = e.event_type_id
             INNER JOIN venues v ON v.venue_id = e.venue_id
             WHERE td.ticket_type = 'event_ticket'
               AND LOWER(et.name) = :cat
             ORDER BY
                CASE LOWER(COALESCE(e.event_day,''))
                    WHEN 'thursday' THEN 1 WHEN 'friday' THEN 2 WHEN 'saturday' THEN 3 WHEN 'sunday' THEN 4 ELSE 9
                END,
                COALESCE(e.start_time,'99:99') ASC"
        );
        $stmt->execute(['cat' => $category]);
        /** @var list<array<string,mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
            'sunday' => [],
        ];

        foreach ($rows as $r) {
            $day = strtolower(trim((string) ($r['event_day'] ?? 'friday')));
            if (!isset($grouped[$day])) {
                $grouped[$day] = [];
            }
            $grouped[$day][] = [
                'ticket_details_id' => (int) $r['ticket_details_id'],
                'name' => (string) $r['name'],
                'subtitle' => (string) ($r['description'] ?? ''),
                'price' => (string) $r['price'],
                'is_free' => (bool) (int) $r['is_free'],
                'event_day' => $day,
                'start_time' => $r['start_time'] !== null ? (string) $r['start_time'] : '',
                'end_time' => $r['end_time'] !== null ? (string) $r['end_time'] : '',
                'venue_name' => (string) $r['venue_name'],
                'hall' => $r['hall'] !== null ? (string) $r['hall'] : '',
            ];
        }

        return $grouped;
    }

    /** @param array<string,mixed> $r */
    private function normalizeRow(array $r): array
    {
        return [
            'ticket_details_id' => (int) $r['ticket_details_id'],
            'ticket_type' => (string) $r['ticket_type'],
            'category' => (string) ($r['category'] ?? 'all'),
            'pass_day' => $r['pass_day'] !== null ? (string) $r['pass_day'] : '',
            'pass_time' => $r['pass_time'] !== null ? (string) $r['pass_time'] : '',
            'schedule_display' => $r['schedule_display'] !== null ? (string) $r['schedule_display'] : '',
            'sort_order' => (int) ($r['sort_order'] ?? 0),
            'is_free' => (bool) (int) ($r['is_free'] ?? 0),
            'name' => (string) $r['name'],
            'description' => $r['description'] !== null ? (string) $r['description'] : '',
            'price' => (string) $r['price'],
        ];
    }
}
