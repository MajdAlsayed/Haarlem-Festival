<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;
use PDO;
use PDOStatement;
use Throwable;

final class TicketsRepository extends Repository
{
    public const CATEGORIES = ['jazz', 'dance', 'history', 'stories'];

    private const DANCE_DAYS = ['thursday', 'friday', 'saturday', 'sunday'];

    private const DEFAULT_INTRO = 'Explore all events from History tours to Jazz concerts, Dance nights, and Stories performances.';

    private const PASS_COLUMNS = 'ticket_details_id, ticket_type, category, pass_day, pass_time, schedule_display,
                    sort_order, is_free, name, description, price';

    public function getIntroText(): string
    {
        try {
            $stmt = $this->db->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1');
            $stmt->execute(['k' => 'tickets_intro']);
            $value = $stmt->fetchColumn();
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        } catch (Throwable) {
        }

        return self::DEFAULT_INTRO;
    }

    // day pass + all access for /tickets tab
    public function getPassesForCategory(string $category): array
    {
        $stmt = $this->db->prepare(
            'SELECT ' . self::PASS_COLUMNS . "
             FROM ticket_details
             WHERE ticket_type IN ('day_pass', 'all_access_pass')
               AND (LOWER(category) = :cat OR LOWER(category) = 'all')
             ORDER BY sort_order ASC, ticket_details_id ASC"
        );
        $stmt->execute(['cat' => $this->normalizeCategory($category)]);

        return array_map(fn (array $row): array => $this->normalizeRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // dance event page — matching pass_day
    public function getDanceDayPassForDay(string $eventDay): ?array
    {
        $day = strtolower(trim($eventDay));
        if (!in_array($day, self::DANCE_DAYS, true)) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT ' . self::PASS_COLUMNS . "
             FROM ticket_details
             WHERE ticket_type = 'day_pass'
               AND LOWER(category) = 'dance'
               AND LOWER(COALESCE(pass_day, '')) = :day
             LIMIT 1"
        );
        $stmt->execute(['day' => $day]);

        return $this->fetchNormalizedRow($stmt);
    }

    public function getDanceAllAccessPass(): ?array
    {
        $stmt = $this->db->query(
            'SELECT ' . self::PASS_COLUMNS . "
             FROM ticket_details
             WHERE ticket_type = 'all_access_pass'
               AND LOWER(category) = 'dance'
             LIMIT 1"
        );

        return $this->fetchNormalizedRow($stmt);
    }

    public function getEventTicketsGroupedByDay(string $category): array
    {
        $stmt = $this->db->prepare(
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
                    e.hall, v.name AS venue_name
             FROM ticket_details td
             INNER JOIN events e ON e.event_id = td.event_id
             LEFT JOIN sessions s ON s.session_id = td.session_id
             INNER JOIN event_types et ON et.event_type_id = e.event_type_id
             INNER JOIN venues v ON v.venue_id = e.venue_id
             WHERE td.ticket_type = 'event_ticket'
               AND LOWER(et.name) = :cat
             ORDER BY
                CASE LOWER(COALESCE(e.event_day, ''))
                    WHEN 'thursday' THEN 1 WHEN 'friday' THEN 2 WHEN 'saturday' THEN 3 WHEN 'sunday' THEN 4 ELSE 9
                END,
                COALESCE(e.start_time, '99:99') ASC"
        );
        $stmt->execute(['cat' => $this->normalizeCategory($category)]);

        $grouped = [
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
            'sunday' => [],
        ];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $day = $this->eventDayKey($row);
            if (!isset($grouped[$day])) {
                $grouped[$day] = [];
            }

            $grouped[$day][] = $this->mapEventTicketRow($row, $day);
        }

        return $grouped;
    }

    private function fetchNormalizedRow(PDOStatement $stmt): ?array
    {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return $this->normalizeRow($row);
    }

    private function normalizeRow(array $row): array
    {
        return [
            'ticket_details_id' => (int) $row['ticket_details_id'],
            'ticket_type' => (string) $row['ticket_type'],
            'category' => $this->categoryValue($row),
            'pass_day' => $this->textOrEmpty($this->rowValue($row, 'pass_day')),
            'pass_time' => $this->textOrEmpty($this->rowValue($row, 'pass_time')),
            'schedule_display' => $this->textOrEmpty($this->rowValue($row, 'schedule_display')),
            'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
            'is_free' => (bool) (int) (isset($row['is_free']) ? $row['is_free'] : 0),
            'name' => (string) $row['name'],
            'description' => $this->textOrEmpty($this->rowValue($row, 'description')),
            'price' => (string) $row['price'],
        ];
    }

    private function mapEventTicketRow(array $row, string $day): array
    {
        return [
            'ticket_details_id' => (int) $row['ticket_details_id'],
            'name' => (string) $row['name'],
            'subtitle' => $this->rowText($row, 'description'),
            'price' => (string) $row['price'],
            'is_free' => (bool) (int) $row['is_free'],
            'event_day' => $day,
            'start_time' => $this->textOrEmpty($this->rowValue($row, 'start_time')),
            'end_time' => $this->textOrEmpty($this->rowValue($row, 'end_time')),
            'venue_name' => (string) $row['venue_name'],
            'hall' => $this->textOrEmpty($this->rowValue($row, 'hall')),
        ];
    }

    private function eventDayKey(array $row): string
    {
        $day = strtolower(trim($this->rowText($row, 'event_day')));

        return $day !== '' ? $day : 'friday';
    }

    private function normalizeCategory(string $category): string
    {
        $category = strtolower(trim($category));
        if (!in_array($category, self::CATEGORIES, true)) {
            return 'jazz';
        }

        return $category;
    }

    private function categoryValue(array $row): string
    {
        $category = $this->rowText($row, 'category');

        return $category !== '' ? $category : 'all';
    }

    private function rowText(array $row, string $key): string
    {
        return isset($row[$key]) ? (string) $row[$key] : '';
    }

    private function rowValue(array $row, string $key): mixed
    {
        return array_key_exists($key, $row) ? $row[$key] : null;
    }

    private function textOrEmpty(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}
