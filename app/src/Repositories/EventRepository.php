<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Event;

class EventRepository
{
    public function getByCategory(string $category): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT id, category, title, description, location
             FROM events
             WHERE category = :category'
        );

        $stmt->execute(['category' => $category]);
        $rows = $stmt->fetchAll();

        $events = [];

        foreach ($rows as $row) {
            $event = new Event();
            $event->id = $row['id'];
            $event->category = $row['category'];
            $event->title = $row['title'];
            $event->description = $row['description'];
            $event->location = $row['location'];

            $events[] = $event;
        }

        return $events;
    }
}
