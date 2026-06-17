<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;
use InvalidArgumentException;
use PDO;
use RuntimeException;

final class DanceCmsRepository extends Repository
{
    private const EVENT_COLUMNS = '
        e.event_id, e.event_type_id, e.venue_id, e.title, e.description, e.event_day,
        e.start_time, e.end_time, e.hall, e.seats, e.price,
        v.name AS venue_name, v.city AS venue_city
    ';

    private ?int $danceEventTypeId = null;

    // cached so we dont hit event_types every time
    public function getDanceEventTypeId(): int
    {
        if ($this->danceEventTypeId !== null) {
            return $this->danceEventTypeId;
        }

        $stmt = $this->db->query("SELECT event_type_id FROM event_types WHERE LOWER(name) = 'dance' LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new RuntimeException('Dance event type is missing. Check event_types seed/migrations.');
        }

        $this->danceEventTypeId = (int) $row['event_type_id'];

        return $this->danceEventTypeId;
    }

    public function listVenues(): array
    {
        $stmt = $this->db->query('SELECT venue_id, name, city FROM venues ORDER BY name ASC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $row): array => $this->mapVenueRow($row), $rows);
    }

    public function listDanceEventsForAdmin(): array
    {
        $sql = '
            SELECT ' . self::EVENT_COLUMNS . '
            FROM events e
            JOIN venues v ON v.venue_id = e.venue_id
            WHERE e.event_type_id = :tid
            ORDER BY
                CASE LOWER(COALESCE(e.event_day, \'\'))
                    WHEN \'thursday\' THEN 1 WHEN \'friday\' THEN 2 WHEN \'saturday\' THEN 3 WHEN \'sunday\' THEN 4 ELSE 9
                END,
                COALESCE(e.start_time, \'99:99\') ASC, e.event_id ASC
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tid' => $this->getDanceEventTypeId()]);

        return array_map(fn (array $row): array => $this->normalizeEventRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getDanceEventById(int $eventId): ?array
    {
        if ($eventId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT ' . self::EVENT_COLUMNS . '
             FROM events e
             JOIN venues v ON v.venue_id = e.venue_id
             WHERE e.event_id = :id AND e.event_type_id = :tid
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $eventId,
            'tid' => $this->getDanceEventTypeId(),
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return $this->normalizeEventRow($row);
    }

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
            ...$this->eventWriteValues($venueId, $title, $description, $eventDay, $startTime, $endTime, $hall, $seats, $price),
            'eid' => $eventId,
        ]);
    }

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
        $stmt = $this->db->prepare(
            'INSERT INTO events (event_type_id, venue_id, title, description, event_day, start_time, end_time, hall, seats, price)
             VALUES (:tid, :vid, :title, :desc, :day, :st, :et, :hall, :seats, :price)'
        );
        $stmt->execute([
            'tid' => $this->getDanceEventTypeId(),
            ...$this->eventWriteValues($venueId, $title, $description, $eventDay, $startTime, $endTime, $hall, $seats, $price),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function deleteDanceEvent(int $eventId): void
    {
        $this->assertDanceEvent($eventId);

        $stmt = $this->db->prepare('DELETE FROM events WHERE event_id = :id');
        $stmt->execute(['id' => $eventId]);
    }

    // block editing jazz/history events through this repo
    private function assertDanceEvent(int $eventId): void
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM events WHERE event_id = :id AND event_type_id = :tid LIMIT 1'
        );
        $stmt->execute([
            'id' => $eventId,
            'tid' => $this->getDanceEventTypeId(),
        ]);

        if ($stmt->fetchColumn() === false) {
            throw new InvalidArgumentException('Dance event not found.');
        }
    }

    private function mapVenueRow(array $row): array
    {
        return [
            'venue_id' => (int) $row['venue_id'],
            'name' => (string) $row['name'],
            'city' => (string) $row['city'],
        ];
    }

    private function normalizeEventRow(array $row): array
    {
        return [
            'event_id' => (int) $row['event_id'],
            'event_type_id' => (int) $row['event_type_id'],
            'venue_id' => (int) $row['venue_id'],
            'title' => (string) $row['title'],
            'description' => $this->textOrEmpty($this->rowValue($row, 'description')),
            'event_day' => $this->eventDayText($this->rowValue($row, 'event_day')),
            'start_time' => $this->textOrEmpty($this->rowValue($row, 'start_time')),
            'end_time' => $this->textOrEmpty($this->rowValue($row, 'end_time')),
            'hall' => $this->textOrEmpty($this->rowValue($row, 'hall')),
            'seats' => $this->nullableInt($row, 'seats'),
            'price' => $this->nullableStoredText($this->nullableRowString($row, 'price')),
            'venue_name' => $this->rowText($row, 'venue_name'),
            'venue_city' => $this->rowText($row, 'venue_city'),
        ];
    }

    private function eventWriteValues(
        int $venueId,
        string $title,
        string $description,
        string $eventDay,
        string $startTime,
        ?string $endTime,
        ?string $hall,
        ?int $seats,
        ?string $price
    ): array {
        return [
            'vid' => $venueId,
            'title' => $title,
            'desc' => $this->nullableStoredText($description),
            'day' => strtolower(trim($eventDay)),
            'st' => $startTime,
            'et' => $this->nullableStoredText($endTime),
            'hall' => $this->nullableStoredText($hall),
            'seats' => $seats,
            'price' => $this->nullableStoredText($price),
        ];
    }

    private function nullableStoredText(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }

    private function textOrEmpty(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    private function eventDayText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return strtolower(trim((string) $value));
    }

    private function rowText(array $row, string $key): string
    {
        return isset($row[$key]) ? (string) $row[$key] : '';
    }

    private function rowValue(array $row, string $key): mixed
    {
        return array_key_exists($key, $row) ? $row[$key] : null;
    }

    private function nullableRowString(array $row, string $key): ?string
    {
        if (!isset($row[$key]) || $row[$key] === null) {
            return null;
        }

        return (string) $row[$key];
    }

    private function nullableInt(array $row, string $key): ?int
    {
        if (!isset($row[$key]) || $row[$key] === null) {
            return null;
        }

        return (int) $row[$key];
    }
}
