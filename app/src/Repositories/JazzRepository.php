<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class JazzRepository
{
    /**
     * @return array<int, array{
     *   event_id:int,
     *   title:string,
     *   description:?string,
     *   event_day:?string,
     *   start_time:?string,
     *   end_time:?string,
     *   hall:?string,
     *   seats:?int,
     *   price:?string,
     *   venue_name:string,
     *   venue_city:string
     * }>
     */
    public function getAll(): array
    {
        $db = Database::getConnection();

        $baseSql = "
            SELECT
                e.event_id,
                e.title,
                e.description,
                e.event_day,
                e.start_time,
                v.name AS venue_name,
                v.city AS venue_city
            FROM events e
            JOIN event_types et ON e.event_type_id = et.event_type_id
            JOIN venues v ON e.venue_id = v.venue_id
            WHERE LOWER(et.name) = 'jazz'
            ORDER BY
                CASE LOWER(COALESCE(e.event_day,'friday'))
                    WHEN 'thursday' THEN 1
                    WHEN 'friday' THEN 2
                    WHEN 'saturday' THEN 3
                    WHEN 'sunday' THEN 4
                    ELSE 9
                END,
                COALESCE(e.start_time,'99:99') ASC
        ";

        $fullSql = "
            SELECT
                e.event_id,
                e.title,
                e.description,
                e.event_day,
                e.start_time,
                e.end_time,
                e.hall,
                e.seats,
                e.price,
                v.name AS venue_name,
                v.city AS venue_city
            FROM events e
            JOIN event_types et ON e.event_type_id = et.event_type_id
            JOIN venues v ON e.venue_id = v.venue_id
            WHERE LOWER(et.name) = 'jazz'
            ORDER BY
                CASE LOWER(COALESCE(e.event_day,'friday'))
                    WHEN 'thursday' THEN 1
                    WHEN 'friday' THEN 2
                    WHEN 'saturday' THEN 3
                    WHEN 'sunday' THEN 4
                    ELSE 9
                END,
                COALESCE(e.start_time,'99:99') ASC
        ";

        try {
            $stmt = $db->query($fullSql);
        } catch (\Throwable $e) {
            $stmt = $db->query($baseSql);
        }

        /** @var array<int, array<string,mixed>> $rows */
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function ($r) {
            return [
                'event_id' => (int)$r['event_id'],
                'title' => (string)$r['title'],
                'description' => $r['description'] !== null ? (string)$r['description'] : null,
                'event_day' => $r['event_day'] !== null ? strtolower(trim((string)$r['event_day'])) : null,
                'start_time' => $r['start_time'] !== null ? (string)$r['start_time'] : null,
                'end_time' => isset($r['end_time']) && $r['end_time'] !== null ? (string)$r['end_time'] : null,
                'hall' => isset($r['hall']) && $r['hall'] !== null ? (string)$r['hall'] : null,
                'seats' => isset($r['seats']) && $r['seats'] !== null ? (int)$r['seats'] : null,
                'price' => isset($r['price']) && $r['price'] !== null ? (string)$r['price'] : null,
                'venue_name' => (string)$r['venue_name'],
                'venue_city' => (string)$r['venue_city'],
            ];
        }, $rows);
    }

    /** @return array<int, array<string,mixed>> */
    public function getByDay(string $day): array
    {
        $day = strtolower(trim($day));
        $all = $this->getAll();
        return array_values(array_filter($all, fn($e) => ($e['event_day'] ?? '') === $day));
    }

    /** @return array<int, array<string,mixed>> */
    public function getByTitle(string $title): array
    {
        $title = strtolower(trim($title));
        $all = $this->getAll();

        return array_values(array_filter($all, function ($e) use ($title) {
            return strtolower(trim((string)$e['title'])) === $title;
        }));
    }
}