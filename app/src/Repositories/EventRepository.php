<?php

namespace App\Repositories;

use App\Contracts\EventRepositoryInterface;
use App\Core\Database;
use App\Models\Event;

class EventRepository implements EventRepositoryInterface
{
    public function getAll(): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT e.event_id,
                    e.event_type_id,
                    e.venue_id,
                    e.title,
                    e.description,
                    e.event_day,
                    e.start_time,
                    et.name AS event_type_name,
                    et.card_image,
                    et.info_path,
                    v.name AS venue_name,
                    v.city AS venue_city
             FROM events e
             JOIN event_types et ON e.event_type_id = et.event_type_id
             JOIN venues v ON e.venue_id = v.venue_id'
        );

        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $events = [];
        foreach ($rows as $row) {
            $events[] = $this->mapRowToEvent($row);
        }

        return $events;
    }

    public function getByCategory(string $eventTypeName): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT e.event_id,
                    e.event_type_id,
                    e.venue_id,
                    e.title,
                    e.description,
                    e.event_day,
                    e.start_time,
                    et.name AS event_type_name,
                    v.name AS venue_name,
                    v.city AS venue_city
             FROM events e
             JOIN event_types et ON e.event_type_id = et.event_type_id
             JOIN venues v ON e.venue_id = v.venue_id
             WHERE LOWER(et.name) = LOWER(:event_type_name)'
        );

        $stmt->execute(['event_type_name' => $eventTypeName]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $events = [];
        foreach ($rows as $row) {
            $events[] = $this->mapRowToEvent($row);
        }

        return $events;
    }

    public function getByCategoryAndDay(string $eventTypeName, string $eventDay): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT e.event_id,
                    e.event_type_id,
                    e.venue_id,
                    e.title,
                    e.description,
                    e.event_day,
                    e.start_time,
                    et.name AS event_type_name,
                    v.name AS venue_name,
                    v.city AS venue_city
             FROM events e
             JOIN event_types et ON e.event_type_id = et.event_type_id
             JOIN venues v ON e.venue_id = v.venue_id
             WHERE LOWER(et.name) = LOWER(:event_type_name)
               AND LOWER(TRIM(COALESCE(e.event_day, "friday"))) = LOWER(:event_day)'
        );

        $stmt->execute([
            'event_type_name' => $eventTypeName,
            'event_day' => trim($eventDay),
        ]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $events = [];
        foreach ($rows as $row) {
            $events[] = $this->mapRowToEvent($row);
        }

        return $events;
    }

    private function mapRowToEvent(array $row): Event
    {
        $event = new Event();
        $event->id = (int) $row['event_id'];
        $event->eventTypeId = (int) $row['event_type_id'];
        $event->venueId = (int) $row['venue_id'];
        $event->title = $row['title'];
        $event->description = $row['description'] ?? null;
        $event->eventDay = isset($row['event_day']) ? (string) $row['event_day'] : null;
        $event->startTime = isset($row['start_time']) ? (string) $row['start_time'] : null;
        $event->eventTypeName = $row['event_type_name'];
        $event->venueName = $row['venue_name'];
        $event->venueCity = !empty($row['venue_city']) ? $row['venue_city'] : (new SettingsRepository())->getAll()['default_venue_city'];
        $event->cardImage = isset($row['card_image']) ? (string) $row['card_image'] : null;
        $event->infoPath = isset($row['info_path']) ? (string) $row['info_path'] : null;
        return $event;
    }
}
