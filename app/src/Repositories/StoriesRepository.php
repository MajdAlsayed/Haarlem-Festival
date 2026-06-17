<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\IStoriesRepository;
use App\Core\Repository;
use App\Exceptions\AppException;
use PDO;
use PDOException;

class StoriesRepository extends Repository implements IStoriesRepository
{
    /**
     * Get stories filtered by day.
     *
     * @throws AppException If database query fails.
     */
    public function getStories(?string $day = null): array
    {
        try {
            $sql = $this->baseStorySelect();
            $params = [];

            if (!empty($day) && strtolower(trim($day)) !== 'all') {
                $sql .= ' WHERE LOWER(TRIM(s.event_day)) = :day';
                $params['day'] = strtolower(trim($day));
            }

            $sql .= "
                ORDER BY
                    FIELD(LOWER(s.event_day), 'thursday','friday','saturday','sunday'),
                    s.start_time ASC
            ";

            $statement = $this->db->prepare($sql);
            $statement->execute($params);

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            throw new AppException('Unable to fetch stories. Please try again later.');
        }
    }

    /**
     * Get a single story by ID.
     *
     * @throws AppException If database query fails.
     */
    public function getStoryById(int $storyId): ?array
    {
        try {
            $statement = $this->db->prepare($this->baseStorySelect() . '
                WHERE s.story_id = :id
                LIMIT 1
            ');

            $statement->execute(['id' => $storyId]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (PDOException) {
            throw new AppException('Unable to fetch story data. Please try again later.');
        }
    }

    /**
     * Get all stories for the admin panel.
     *
     * @throws AppException If database query fails.
     */
    public function getAllStoriesForAdmin(): array
    {
        try {
            $statement = $this->db->query("
                SELECT
                    story_id, name, slug, description,
                    image_path, story_type, age, language, template,
                    COALESCE(audience, '') AS audience,
                    event_id, event_day, start_time, end_time
                FROM stories
                ORDER BY story_id ASC
            ");

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            throw new AppException('Unable to fetch stories. Please try again later.');
        }
    }

    /**
     * Get story by slug.
     *
     * @throws AppException If database query fails.
     */
    public function getStoryBySlug(string $slug): ?array
    {
        try {
            $statement = $this->db->prepare("
                SELECT
                    story_id, name, slug, description,
                    image_path, story_type, age, language, template,
                    COALESCE(audience, '') AS audience,
                    event_id, event_day, start_time, end_time
                FROM stories
                WHERE slug = :slug
                LIMIT 1
            ");

            $statement->execute(['slug' => trim($slug)]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (PDOException) {
            throw new AppException('Unable to fetch story data. Please try again later.');
        }
    }

    /**
     * Create a new story.
     *
     * @throws AppException If database insert fails.
     */
    public function createStory(array $data): int
    {
        try {
            $eventId = (int)($data['event_id'] ?? 0);
            $venueId = $this->getVenueIdForEvent($eventId);
            $params = $this->buildStoryParams($data, $eventId, $venueId);

            $statement = $this->db->prepare("
                INSERT INTO stories
                    (name, slug, description, image_path, story_type, age, language, template, audience, event_id, venue_id, event_day, start_time, end_time)
                VALUES
                    (:name, :slug, :description, :image_path, :story_type, :age, :language, :template, :audience, :event_id, :venue_id, :event_day, :start_time, :end_time)
            ");

            $statement->execute($params);

            return (int)$this->db->lastInsertId();
        } catch (PDOException) {
            throw new AppException('Unable to create story. Please try again later.');
        }
    }

    /**
     * Update an existing story.
     *
     * @throws AppException If database update fails.
     */
    public function updateStory(int $storyId, array $data): bool
    {
        try {
            $eventId = (int)($data['event_id'] ?? 0);
            $venueId = $this->getVenueIdForEvent($eventId);
            $params = $this->buildStoryParams($data, $eventId, $venueId);
            $params['story_id'] = $storyId;

            $statement = $this->db->prepare("
                UPDATE stories SET
                    name        = :name,
                    slug        = :slug,
                    description = :description,
                    image_path  = :image_path,
                    story_type  = :story_type,
                    age         = :age,
                    language    = :language,
                    template    = :template,
                    audience    = :audience,
                    event_id    = :event_id,
                    venue_id    = :venue_id,
                    event_day   = :event_day,
                    start_time  = :start_time,
                    end_time    = :end_time
                WHERE story_id  = :story_id
            ");

            return $statement->execute($params);
        } catch (PDOException) {
            throw new AppException('Unable to update story. Please try again later.');
        }
    }

    /**
     * Delete a story.
     *
     * @throws AppException If database delete fails.
     */
    public function deleteStory(int $storyId): bool
    {
        try {
            $statement = $this->db->prepare('DELETE FROM stories WHERE story_id = :story_id');

            return $statement->execute(['story_id' => $storyId]);
        } catch (PDOException) {
            throw new AppException('Unable to delete story. Please try again later.');
        }
    }

    /**
     * Get detail page by story ID.
     *
     * @throws AppException If database query fails.
     */
    public function getDetailPageByStoryId(int $storyId): ?array
    {
        try {
            $statement = $this->db->prepare("
                SELECT
                    detail_page_id,
                    story_id,
                    hero_image,
                    hero_heading,
                    hero_description,
                    article_title,
                    article_image,
                    article_image_caption,
                    article_paragraph_1,
                    article_paragraph_2,
                    article_paragraph_3,
                    highlights,
                    gallery,
                    created_at,
                    updated_at
                FROM story_detail_pages
                WHERE story_id = :story_id
                LIMIT 1
            ");

            $statement->execute(['story_id' => $storyId]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (PDOException) {
            throw new AppException('Unable to fetch story detail page. Please try again later.');
        }
    }

    /**
     * Check if story has a detail page.
     *
     * @throws AppException If database query fails.
     */
    public function hasDetailPage(int $storyId): bool
    {
        try {
            $statement = $this->db->prepare("
                SELECT 1 FROM story_detail_pages
                WHERE story_id = :story_id
                LIMIT 1
            ");

            $statement->execute(['story_id' => $storyId]);

            return (bool)$statement->fetchColumn();
        } catch (PDOException) {
            throw new AppException('Unable to check story detail page. Please try again later.');
        }
    }

    /**
     * Save or update story detail page.
     *
     * @throws AppException If database operation fails.
     */
    public function saveDetailPage(int $storyId, array $data): bool
    {
        try {
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

            $statement = $this->db->prepare($sql);

            return $statement->execute([
                'story_id' => $storyId,
                'hero_image' => $data['hero_image'] ?? null,
                'hero_heading' => $data['hero_heading'] ?? null,
                'hero_description' => $data['hero_description'] ?? null,
                'article_title' => $data['article_title'] ?? null,
                'article_image' => $data['article_image'] ?? null,
                'article_image_caption' => $data['article_image_caption'] ?? null,
                'article_paragraph_1' => $data['article_paragraph_1'] ?? null,
                'article_paragraph_2' => $data['article_paragraph_2'] ?? null,
                'article_paragraph_3' => $data['article_paragraph_3'] ?? null,
                'highlights' => $data['highlights'] ?? null,
                'gallery' => $data['gallery'] ?? null,
            ]);
        } catch (PDOException) {
            throw new AppException('Unable to save story detail page. Please try again later.');
        }
    }

    private function baseStorySelect(): string
    {
        return "
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
                COALESCE(s.audience, '') AS audience,
                s.event_id,
                s.event_day,
                s.start_time,
                s.end_time,
                e.title AS event_title,
                v.name AS venue_name,
                v.address AS venue_address,
                v.city AS venue_city,
                (
                    SELECT td.ticket_details_id
                    FROM ticket_details td
                    WHERE td.event_id = s.event_id
                      AND td.ticket_type = 'event_ticket'
                    ORDER BY td.sort_order ASC, td.ticket_details_id ASC
                    LIMIT 1
                ) AS ticket_details_id
            FROM stories s
            LEFT JOIN events e ON e.event_id = s.event_id
            LEFT JOIN venues v ON v.venue_id = s.venue_id
        ";
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStoryParams(array $data, int $eventId, int $venueId): array
    {
        $template = trim((string)($data['template'] ?? ''));

        return [
            'name' => (string)($data['name'] ?? ''),
            'slug' => (string)($data['slug'] ?? ''),
            'description' => (string)($data['description'] ?? ''),
            'image_path' => (string)($data['image_path'] ?? ''),
            'story_type' => (string)($data['story_type'] ?? ''),
            'age' => (string)($data['age'] ?? ''),
            'language' => (string)($data['language'] ?? ''),
            'template' => $template !== '' ? $template : 'generic',
            'audience' => (string)($data['audience'] ?? ''),
            'event_id' => $eventId,
            'venue_id' => $venueId,
            'event_day' => $this->nullableString($data, 'event_day'),
            'start_time' => $this->nullableString($data, 'start_time'),
            'end_time' => $this->nullableString($data, 'end_time'),
        ];
    }

    private function nullableString(array $data, string $key): ?string
    {
        $value = trim((string)($data[$key] ?? ''));

        return $value === '' ? null : $value;
    }

    private function getVenueIdForEvent(int $eventId): int
    {
        if ($eventId <= 0) {
            return 0;
        }

        $statement = $this->db->prepare('SELECT venue_id FROM events WHERE event_id = :event_id LIMIT 1');
        $statement->execute(['event_id' => $eventId]);

        return (int)$statement->fetchColumn();
    }
}
