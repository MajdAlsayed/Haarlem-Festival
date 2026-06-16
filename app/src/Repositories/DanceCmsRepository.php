<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;
use PDO;

/** Dance event CRUD for admin (event_types.name = dance). Mirrors JazzCmsRepository. */
final class DanceCmsRepository extends Repository
{
    /** Resolve event_types id for "dance" once per operation. */
    public function getDanceEventTypeId(): int
    {
        $stmt = $this->db->query("SELECT event_type_id FROM event_types WHERE LOWER(name) = 'dance' LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \RuntimeException('Dance event type is missing. Check event_types seed/migrations.');
        }

        return (int) $row['event_type_id'];
    }

    /** @return list<array{venue_id:int,name:string,city:string}> */
    public function listVenues(): array
    {
        $stmt = $this->db->query('SELECT venue_id, name, city FROM venues ORDER BY name ASC');
        /** @var list<array<string,mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static fn (array $r): array => [
            'venue_id' => (int) $r['venue_id'],
            'name' => (string) $r['name'],
            'city' => (string) $r['city'],
        ], $rows);
    }

    /**
     * @return list<array<string,mixed>>
     */
    /** Admin grid source: all dance events with venue details and predictable day/time ordering. */
    public function listDanceEventsForAdmin(): array
    {
        $danceId = $this->getDanceEventTypeId();
        $sql = '
            SELECT e.event_id, e.event_type_id, e.venue_id, e.title, e.description, e.event_day,
                   e.start_time, e.end_time, e.hall, e.seats, e.price,
                   v.name AS venue_name, v.city AS venue_city
            FROM events e
            JOIN venues v ON v.venue_id = e.venue_id
            WHERE e.event_type_id = :tid
            ORDER BY
                CASE LOWER(COALESCE(e.event_day,\'\'))
                    WHEN \'thursday\' THEN 1 WHEN \'friday\' THEN 2 WHEN \'saturday\' THEN 3 WHEN \'sunday\' THEN 4 ELSE 9
                END,
                COALESCE(e.start_time,\'99:99\') ASC, e.event_id ASC
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tid' => $danceId]);
        /** @var list<array<string,mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $r): array => $this->normalizeEventRow($r), $rows);
    }

    /** @return ?array<string,mixed> */
    /** Fetch one dance event for edit pages; null when id/type doesn't match dance. */
    public function getDanceEventById(int $eventId): ?array
    {
        if ($eventId <= 0) {
            return null;
        }
        $danceId = $this->getDanceEventTypeId();
        $stmt = $this->db->prepare(
            'SELECT e.event_id, e.event_type_id, e.venue_id, e.title, e.description, e.event_day,
                    e.start_time, e.end_time, e.hall, e.seats, e.price,
                    v.name AS venue_name, v.city AS venue_city
             FROM events e
             JOIN venues v ON v.venue_id = e.venue_id
             WHERE e.event_id = :id AND e.event_type_id = :tid
             LIMIT 1'
        );
        $stmt->execute(['id' => $eventId, 'tid' => $danceId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            return null;
        }

        return $this->normalizeEventRow($r);
    }

    /** @return array<string,mixed> */
    /** Normalize DB row types (ints, nullable strings) for controller/view usage. */
    private function normalizeEventRow(array $r): array
    {
        return [
            'event_id' => (int) $r['event_id'],
            'event_type_id' => (int) $r['event_type_id'],
            'venue_id' => (int) $r['venue_id'],
            'title' => (string) $r['title'],
            'description' => $r['description'] !== null ? (string) $r['description'] : '',
            'event_day' => $r['event_day'] !== null ? strtolower(trim((string) $r['event_day'])) : '',
            'start_time' => $r['start_time'] !== null ? (string) $r['start_time'] : '',
            'end_time' => $r['end_time'] !== null ? (string) $r['end_time'] : '',
            'hall' => $r['hall'] !== null ? (string) $r['hall'] : '',
            'seats' => isset($r['seats']) && $r['seats'] !== null ? (int) $r['seats'] : null,
            'price' => isset($r['price']) && $r['price'] !== null ? (string) $r['price'] : null,
            'venue_name' => (string) ($r['venue_name'] ?? ''),
            'venue_city' => (string) ($r['venue_city'] ?? ''),
        ];
    }

    /** Update existing dance event row from admin form payload. */
    public function updateDanceEvent(
        int $eventId,
        int $venueId,
        string $title,
        string $description,
        string $eventDay,
        string $startTime,
        ?string $endTime,
        ?string $hall,
        ?int $seats,
        ?string $price
    ): void {
        $this->assertDanceEvent($eventId);
        $stmt = $this->db->prepare(
            'UPDATE events SET venue_id = :vid, title = :title, description = :desc, event_day = :day,
             start_time = :st, end_time = :et, hall = :hall, seats = :seats, price = :price
             WHERE event_id = :eid'
        );
        $stmt->execute([
            'vid' => $venueId,
            'title' => $title,
            'desc' => $description === '' ? null : $description,
            'day' => strtolower(trim($eventDay)),
            'st' => $startTime,
            'et' => $endTime === null || $endTime === '' ? null : $endTime,
            'hall' => $hall === null || $hall === '' ? null : $hall,
            'seats' => $seats,
            'price' => $price === null || $price === '' ? null : $price,
            'eid' => $eventId,
        ]);
    }

    /** Insert a new dance event and return its event_id. */
    public function createDanceEvent(
        int $venueId,
        string $title,
        string $description,
        string $eventDay,
        string $startTime,
        ?string $endTime,
        ?string $hall,
        ?int $seats,
        ?string $price
    ): int {
        $tid = $this->getDanceEventTypeId();
        $stmt = $this->db->prepare(
            'INSERT INTO events (event_type_id, venue_id, title, description, event_day, start_time, end_time, hall, seats, price)
             VALUES (:tid, :vid, :title, :desc, :day, :st, :et, :hall, :seats, :price)'
        );
        $stmt->execute([
            'tid' => $tid,
            'vid' => $venueId,
            'title' => $title,
            'desc' => $description === '' ? null : $description,
            'day' => strtolower(trim($eventDay)),
            'st' => $startTime,
            'et' => $endTime === null || $endTime === '' ? null : $endTime,
            'hall' => $hall === null || $hall === '' ? null : $hall,
            'seats' => $seats,
            'price' => $price === null || $price === '' ? null : $price,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** Delete event only when it belongs to dance type. */
    public function deleteDanceEvent(int $eventId): void
    {
        $this->assertDanceEvent($eventId);
        $stmt = $this->db->prepare('DELETE FROM events WHERE event_id = :id');
        $stmt->execute(['id' => $eventId]);
    }

    /** Safety check so admin cannot mutate non-dance events from this repo. */
    private function assertDanceEvent(int $eventId): void
    {
        if ($this->getDanceEventById($eventId) === null) {
            throw new \InvalidArgumentException('Dance event not found.');
        }
    }
}
