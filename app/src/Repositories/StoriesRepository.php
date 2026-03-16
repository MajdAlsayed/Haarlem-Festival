<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class StoriesRepository
{
    /**
     * Hardcoded venue map because there is no venues table.
     * Make sure these IDs match the venue_id values used in EventSeeder.
     */
    private function venueMap(): array
    {
        return [
            1 => [
                'venue_id' => 1,
                'name' => 'Verhalenhuis Haarlem',
                'slug' => 'verhalenhuis-haarlem',
                'address' => '',
                'city' => 'Haarlem',
            ],
            2 => [
                'venue_id' => 2,
                'name' => 'De Schuur',
                'slug' => 'de-schuur',
                'address' => '',
                'city' => 'Haarlem',
            ],
            3 => [
                'venue_id' => 3,
                'name' => 'Kweekcafe',
                'slug' => 'kweekcafe',
                'address' => '',
                'city' => 'Haarlem',
            ],
            4 => [
                'venue_id' => 4,
                'name' => 'Corrie ten Boom huis',
                'slug' => 'corrie-ten-boom-huis',
                'address' => '',
                'city' => 'Haarlem',
            ],
            5 => [
                'venue_id' => 5,
                'name' => 'Theater Elswout',
                'slug' => 'theater-elswout',
                'address' => '',
                'city' => 'Haarlem',
            ],
        ];
    }

    private function venueById(int $venueId): array
    {
        $map = $this->venueMap();

        return $map[$venueId] ?? [
            'venue_id' => $venueId,
            'name' => 'Unknown Venue',
            'slug' => 'unknown-venue',
            'address' => '',
            'city' => 'Haarlem',
        ];
    }

    private function venueBySlugInternal(string $slug): ?array
    {
        $slug = strtolower(trim($slug));

        foreach ($this->venueMap() as $venue) {
            if (($venue['slug'] ?? '') === $slug) {
                return $venue;
            }
        }

        return null;
    }

    private function addVenueData(array $row): array
    {
        $venueId = (int)($row['venue_id'] ?? 0);
        $venue = $this->venueById($venueId);

        $row['venue_name'] = $venue['name'];
        $row['venue_slug'] = $venue['slug'];
        $row['venue_address'] = $venue['address'];
        $row['venue_city'] = $venue['city'];

        return $row;
    }

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
                e.end_time,

                s.story_id,
                s.name AS story_name,
                s.slug AS story_slug,
                s.image_path,
                s.story_type,
                s.age,
                s.language
            FROM events e
            LEFT JOIN stories s ON s.event_id = e.event_id
            WHERE e.event_type_id = 5
        ";

        $params = [];

        if ($day && strtolower(trim($day)) !== 'all') {
            $sql .= " AND LOWER(TRIM(e.event_day)) = :day ";
            $params['day'] = strtolower(trim($day));
        }

        $sql .= "
            ORDER BY
                FIELD(LOWER(e.event_day), 'thursday','friday','saturday','sunday'),
                e.start_time ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => $this->addVenueData($row), $rows);
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
                e.end_time,

                s.story_id,
                s.name AS story_name,
                s.slug AS story_slug,
                s.image_path,
                s.story_type,
                s.age,
                s.language
            FROM events e
            LEFT JOIN stories s ON s.event_id = e.event_id
            WHERE e.event_type_id = 5
              AND e.venue_id = :venueId
        ";

        $params = ['venueId' => $venueId];

        if ($day && strtolower(trim($day)) !== 'all') {
            $sql .= " AND LOWER(TRIM(e.event_day)) = :day ";
            $params['day'] = strtolower(trim($day));
        }

        $sql .= "
            ORDER BY
                FIELD(LOWER(e.event_day), 'thursday','friday','saturday','sunday'),
                e.start_time ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => $this->addVenueData($row), $rows);
    }

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
                e.end_time,
                e.title AS event_title
            FROM stories s
            LEFT JOIN events e ON e.event_id = s.event_id
            WHERE s.story_id = :id
            LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $storyId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->addVenueData($row);
    }

    public function getVenueBySlug(string $slug): ?array
    {
        return $this->venueBySlugInternal($slug);
    }
}