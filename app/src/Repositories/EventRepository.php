<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Event;

class EventRepository
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
                    et.name AS event_type_name,
                    v.name AS venue_name
             FROM events e
             JOIN event_types et ON e.event_type_id = et.event_type_id
             JOIN venues v ON e.venue_id = v.venue_id'
        );

        $stmt->execute();
        $rows = $stmt->fetchAll();

        $events = [];

        foreach ($rows as $row) {
            $event = new Event();
            $event->id = $row['event_id'];
            $event->eventTypeId = $row['event_type_id'];
            $event->venueId = $row['venue_id'];
            $event->title = $row['title'];
            $event->description = $row['description'];

            $event->eventTypeName = $row['event_type_name'];
            $event->venueName = $row['venue_name'];

            $events[] = $event;
        }

        return $events;
    }

}
