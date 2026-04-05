<?php

namespace App\Repositories;

use App\Contracts\IStoriesRepository;
use App\Core\Repository;
use PDO;

class StoriesRepository extends Repository implements IStoriesRepository
{

    private function venueMap(): array
    {
        return [
            1 => ['venue_id' => 1, 'name' => 'Verhalenhuis Haarlem', 'slug' => 'verhalenhuis-haarlem', 'address' => '', 'city' => 'Haarlem'],
            2 => ['venue_id' => 2, 'name' => 'De Schuur',            'slug' => 'de-schuur',            'address' => '', 'city' => 'Haarlem'],
            3 => ['venue_id' => 3, 'name' => 'Kweekcafe',            'slug' => 'kweekcafe',            'address' => '', 'city' => 'Haarlem'],
            4 => ['venue_id' => 4, 'name' => 'Corrie ten Boom huis', 'slug' => 'corrie-ten-boom-huis', 'address' => '', 'city' => 'Haarlem'],
            5 => ['venue_id' => 5, 'name' => 'Theater Elswout',      'slug' => 'theater-elswout',      'address' => '', 'city' => 'Haarlem'],
        ];
    }

    private function venueById(int $venueId): array
    {
        // Defensive: use isset to check key exists before returning
        return isset($this->venueMap()[$venueId])
            ? $this->venueMap()[$venueId]
            : [
                'venue_id' => $venueId,
                'name'     => 'Unknown Venue',
                'slug'     => 'unknown-venue',
                'address'  => '',
                'city'     => 'Haarlem',
            ];
    }

    private function venueBySlugInternal(string $slug): ?array
    {
        $slug = strtolower(trim($slug));
        foreach ($this->venueMap() as $venue) {
            if (isset($venue['slug']) && $venue['slug'] === $slug) {
                return $venue;
            }
        }
        return null;
    }

    private function addVenueData(array $row): array
    {
        $venueId = isset($row['venue_id']) ? (int)$row['venue_id'] : 0;
        $venue   = $this->venueById($venueId);

        $row['venue_name']    = $venue['name'];
        $row['venue_slug']    = $venue['slug'];
        $row['venue_address'] = $venue['address'];
        $row['venue_city']    = $venue['city'];

        return $row;
    }


    public function getStories(?string $day = null): array
    {
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
                s.name        AS story_name,
                s.slug        AS story_slug,
                s.image_path,
                s.story_type,
                s.age,
                s.language,
                s.template
            FROM events e
            LEFT JOIN stories s ON s.event_id = e.event_id
            WHERE e.event_type_id = 5
        ";

        $params = [];

        if (!empty($day) && strtolower(trim($day)) !== 'all') {
            $sql .= " AND LOWER(TRIM(e.event_day)) = :day";
            $params['day'] = strtolower(trim($day));
        }

        $sql .= "
            ORDER BY
                FIELD(LOWER(e.event_day), 'thursday','friday','saturday','sunday'),
                e.start_time ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map(
            fn(array $row) => $this->addVenueData($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function getStoriesByVenue(int $venueId, ?string $day = null): array
    {
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
                s.name        AS story_name,
                s.slug        AS story_slug,
                s.image_path,
                s.story_type,
                s.age,
                s.language,
                s.template
            FROM events e
            LEFT JOIN stories s ON s.event_id = e.event_id
            WHERE e.event_type_id = 5
              AND e.venue_id = :venueId
        ";

        $params = ['venueId' => $venueId];

        if (!empty($day) && strtolower(trim($day)) !== 'all') {
            $sql .= " AND LOWER(TRIM(e.event_day)) = :day";
            $params['day'] = strtolower(trim($day));
        }

        $sql .= "
            ORDER BY
                FIELD(LOWER(e.event_day), 'thursday','friday','saturday','sunday'),
                e.start_time ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map(
            fn(array $row) => $this->addVenueData($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function getStoryById(int $storyId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                s.story_id,
                s.name,
                s.slug,
                s.description,
                s.image_path,
                s.story_type,
                s.age,
                s.language,
                s.template,
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
        ");

        $stmt->execute(['id' => $storyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->addVenueData($row) : null;
    }

    public function getVenueBySlug(string $slug): ?array
    {
        return $this->venueBySlugInternal($slug);
    }


    public function getAllStoriesForAdmin(): array
    {
        $stmt = $this->db->query("
            SELECT
                story_id, name, slug, description,
                image_path, story_type, age, language, template,
                venue_id, event_id
            FROM stories
            ORDER BY story_id ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStoryBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                story_id, name, slug, description,
                image_path, story_type, age, language, template,
                venue_id, event_id
            FROM stories
            WHERE slug = :slug
            LIMIT 1
        ");

        $stmt->execute(['slug' => trim($slug)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function createStory(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO stories
                (name, slug, description, image_path, story_type, age, language, template, event_id, venue_id)
            VALUES
                (:name, :slug, :description, :image_path, :story_type, :age, :language, :template, :event_id, :venue_id)
        ");

        $stmt->execute([
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'],
            'image_path'  => $data['image_path'],
            'story_type'  => $data['story_type'],
            'age'         => $data['age'],
            'language'    => $data['language'],
            'template'    => isset($data['template']) && $data['template'] !== '' ? $data['template'] : 'generic',
            'event_id'    => (int)($data['event_id']  ?? 0),
            'venue_id'    => (int)($data['venue_id']  ?? 0),
        ]);

        return (int)$this->db->lastInsertId();
    }


    public function updateStory(int $storyId, array $data): bool
    {
        // Only update template if the caller explicitly provides one
        if (isset($data['template']) && $data['template'] !== '') {
            $sql = "
                UPDATE stories SET
                    name        = :name,
                    slug        = :slug,
                    description = :description,
                    image_path  = :image_path,
                    story_type  = :story_type,
                    age         = :age,
                    language    = :language,
                    template    = :template,
                    event_id    = :event_id
                WHERE story_id  = :story_id
            ";

            $params = [
                'name'        => $data['name'],
                'slug'        => $data['slug'],
                'description' => $data['description'],
                'image_path'  => $data['image_path'],
                'story_type'  => $data['story_type'],
                'age'         => $data['age'],
                'language'    => $data['language'],
                'template'    => $data['template'],
                'event_id'    => (int)($data['event_id'] ?? 0),
                'story_id'    => $storyId,
            ];
        } else {
            
            $sql = "
                UPDATE stories SET
                    name        = :name,
                    slug        = :slug,
                    description = :description,
                    image_path  = :image_path,
                    story_type  = :story_type,
                    age         = :age,
                    language    = :language,
                    event_id    = :event_id
                WHERE story_id  = :story_id
            ";

            $params = [
                'name'        => $data['name'],
                'slug'        => $data['slug'],
                'description' => $data['description'],
                'image_path'  => $data['image_path'],
                'story_type'  => $data['story_type'],
                'age'         => $data['age'],
                'language'    => $data['language'],
                'event_id'    => (int)($data['event_id'] ?? 0),
                'story_id'    => $storyId,
            ];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteStory(int $storyId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM stories WHERE story_id = :story_id");
        return $stmt->execute(['story_id' => $storyId]);
    }

    public function getDetailPageByStoryId(int $storyId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM story_detail_pages
            WHERE story_id = :story_id
            LIMIT 1
        ");

        $stmt->execute(['story_id' => $storyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function hasDetailPage(int $storyId): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM story_detail_pages
            WHERE story_id = :story_id
            LIMIT 1
        ");

        $stmt->execute(['story_id' => $storyId]);
        return (bool)$stmt->fetchColumn();
    }

    public function saveDetailPage(int $storyId, array $data): bool
    {
        $existing = $this->getDetailPageByStoryId($storyId);

        if ($existing) {
            $sql = "
                UPDATE story_detail_pages SET
                    hero_image             = :hero_image,
                    hero_heading           = :hero_heading,
                    hero_description       = :hero_description,
                    article_title          = :article_title,
                    article_image          = :article_image,
                    article_image_caption  = :article_image_caption,
                    article_paragraph_1    = :article_paragraph_1,
                    article_paragraph_2    = :article_paragraph_2,
                    article_paragraph_3    = :article_paragraph_3,
                    highlights             = :highlights,
                    gallery                = :gallery
                WHERE story_id = :story_id
            ";
        } else {
            $sql = "
                INSERT INTO story_detail_pages (
                    story_id, hero_image, hero_heading, hero_description,
                    article_title, article_image, article_image_caption,
                    article_paragraph_1, article_paragraph_2, article_paragraph_3,
                    highlights, gallery
                ) VALUES (
                    :story_id, :hero_image, :hero_heading, :hero_description,
                    :article_title, :article_image, :article_image_caption,
                    :article_paragraph_1, :article_paragraph_2, :article_paragraph_3,
                    :highlights, :gallery
                )
            ";
        }

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'story_id'              => $storyId,
            'hero_image'            => $data['hero_image']            ?? null,
            'hero_heading'          => $data['hero_heading']          ?? null,
            'hero_description'      => $data['hero_description']      ?? null,
            'article_title'         => $data['article_title']         ?? null,
            'article_image'         => $data['article_image']         ?? null,
            'article_image_caption' => $data['article_image_caption'] ?? null,
            'article_paragraph_1'   => $data['article_paragraph_1']  ?? null,
            'article_paragraph_2'   => $data['article_paragraph_2']  ?? null,
            'article_paragraph_3'   => $data['article_paragraph_3']  ?? null,
            'highlights'            => $data['highlights']            ?? null,
            'gallery'               => $data['gallery']               ?? null,
        ]);
    }
}