<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\EventRepositoryInterface;
use App\Core\Repository;
use App\Models\Event;
use PDO;

class EventRepository extends Repository implements EventRepositoryInterface
{
    private const DEFAULT_VENUE_CITY = 'Haarlem';

    private ?string $defaultVenueCity = null;

    public function __construct(
        private SettingsRepository $settingsRepository = new SettingsRepository(),
    ) {
        parent::__construct();
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare(
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

        return $this->rowsToEvents($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // dance programme uses category dance
    public function getByCategory(string $eventTypeName): array
    {
        $stmt = $this->db->prepare(
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
             ORDER BY FIELD(e.event_day, "friday", "saturday", "sunday"), e.start_time'
        );

        $stmt->execute(['event_type_name' => $eventTypeName]);

        return $this->rowsToEvents($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getByCategoryAndDay(string $eventTypeName, string $eventDay): array
    {
        $stmt = $this->db->prepare(
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
               AND LOWER(TRIM(COALESCE(e.event_day, "friday"))) = LOWER(:event_day)
             ORDER BY e.start_time'
        );

        $stmt->execute([
            'event_type_name' => $eventTypeName,
            'event_day' => trim($eventDay),
        ]);

        return $this->rowsToEvents($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?Event
    {
        $stmt = $this->db->prepare(
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
                    v.address AS venue_address,
                    v.city AS venue_city
             FROM events e
             JOIN event_types et ON e.event_type_id = et.event_type_id
             JOIN venues v ON e.venue_id = v.venue_id
             WHERE e.event_id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->mapRowToEvent($row);
    }

    private function rowsToEvents(array $rows): array
    {
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
        $event->title = (string) $row['title'];
        $event->description = isset($row['description']) ? (string) $row['description'] : null;
        $event->eventDay = isset($row['event_day']) ? (string) $row['event_day'] : null;
        $event->startTime = isset($row['start_time']) ? (string) $row['start_time'] : null;
        $event->eventTypeName = (string) $row['event_type_name'];
        $event->venueName = (string) $row['venue_name'];
        $event->venueCity = $this->venueCity($row);
        $event->venueAddress = isset($row['venue_address']) ? (string) $row['venue_address'] : null;
        $event->cardImage = isset($row['card_image']) ? (string) $row['card_image'] : null;
        $event->infoPath = isset($row['info_path']) ? (string) $row['info_path'] : null;

        return $event;
    }

    private function venueCity(array $row): string
    {
        if (isset($row['venue_city']) && (string) $row['venue_city'] !== '') {
            return (string) $row['venue_city'];
        }

        return $this->defaultVenueCity();
    }

    // fallback when venue has no city
    private function defaultVenueCity(): string
    {
        if ($this->defaultVenueCity !== null) {
            return $this->defaultVenueCity;
        }

        $settings = $this->settingsRepository->getAll();
        $city = isset($settings['default_venue_city']) && is_string($settings['default_venue_city'])
            ? trim($settings['default_venue_city'])
            : '';

        $this->defaultVenueCity = $city !== '' ? $city : self::DEFAULT_VENUE_CITY;

        return $this->defaultVenueCity;
    }
}
