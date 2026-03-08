<?php

namespace App\Repositories;

use App\Core\Database;

class StoriesRepository
{
    /// events + stories table 
    public function getStories(?string $day = null): array
    {
        $db = Database::getConnection();

        $sql = "
            SELECT
                e.event_id,
                e.event_type_id,
                e.venue_id,
                e.title,
                e.description,
                e.event_day,
                e.start_time,
                v.name AS venue_name,
                v.city AS venue_city,

                s.story_id,
                s.name AS story_name,
                s.slug AS story_slug,
                s.image_path,
                s.story_type,
                s.age,
                s.language
            FROM events e
            JOIN venues v ON e.venue_id = v.venue_id
            LEFT JOIN stories s ON s.event_id = e.event_id
            WHERE e.event_type_id = 5
        ";

        $params = [];

        if ($day && strtolower($day) !== 'all') {
            $sql .= " AND LOWER(TRIM(e.event_day)) = :day ";
            $params['day'] = strtolower(trim($day));
        }

        $sql .= " ORDER BY FIELD(LOWER(e.event_day), 'thursday','friday','saturday','sunday'), e.start_time ASC ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function getStoriesByVenue(int $venueId, ?string $day = null): array
    {
        $db = Database::getConnection();

        $sql = "
            SELECT
                e.event_id,
                e.event_type_id,
                e.venue_id,
                e.title,
                e.description,
                e.event_day,
                e.start_time,
                v.name AS venue_name,
                v.city AS venue_city,

                s.story_id,
                s.name AS story_name,
                s.slug AS story_slug,
                s.image_path,
                s.story_type,
                s.age,
                s.language
            FROM events e
            JOIN venues v ON e.venue_id = v.venue_id
            LEFT JOIN stories s ON s.event_id = e.event_id
            WHERE e.event_type_id = 5
              AND e.venue_id = :venueId
        ";

        $params = ['venueId' => $venueId];

        if ($day && strtolower($day) !== 'all') {
            $sql .= " AND LOWER(TRIM(e.event_day)) = :day ";
            $params['day'] = strtolower(trim($day));
        }

        $sql .= " ORDER BY FIELD(LOWER(e.event_day), 'thursday','friday','saturday','sunday'), e.start_time ASC ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**

     * Getstory by story_id (from stories table) + join event + venue
     */
    public function getStoryById(int $storyId): ?array
    {
        $db = Database::getConnection();

        $sql = "
            SELECT
                s.story_id,
                s.name,
                s.slug,
                s.description,
                s.image_path,
                s.story_type,
                s.age,
                s.language,
                s.venue_id,
                s.event_id,

                e.event_day,
                e.start_time,
                e.title AS event_title,

                v.name AS venue_name,
                v.address AS venue_address,
                v.city AS venue_city
            FROM stories s
            LEFT JOIN events e ON e.event_id = s.event_id
            LEFT JOIN venues v ON v.venue_id = s.venue_id
            WHERE s.story_id = :id
            LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $storyId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getVenueBySlug(string $slug): ?array
    {
        $db = Database::getConnection();

        $sql = "
          SELECT venue_id, name, address, city
          FROM venues
          WHERE LOWER(REPLACE(TRIM(name), ' ', '-')) = :slug
          LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['slug' => strtolower(trim($slug))]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}