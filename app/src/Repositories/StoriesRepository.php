<?php

namespace App\Repositories;

use App\Contracts\IStoriesRepository;
use App\Core\Repository;
use App\Exceptions\AppException;
use PDO;
use PDOException;


class StoriesRepository extends Repository implements IStoriesRepository
{
    /**
     * Get stories filtered by day
     * 
     * @param string|null $day Optional day filter (thursday, friday, saturday, sunday)
     * @return array Array of story records with associated event data
     * @throws AppException If database query fails
     */
    public function getStories(?string $day = null): array
    {
        try {
            // Query stories table directly, LEFT JOIN for optional event data
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
                    s.template,
                    COALESCE(s.audience, '') AS audience,
                    s.event_id,
                    e.event_day,
                    e.start_time,
                    e.end_time,
                    e.title AS event_title,
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
            ";

            $params = [];

            if (!empty($day) && strtolower(trim($day)) !== 'all') 
            {
                $sql .= " WHERE LOWER(TRIM(e.event_day)) = :day";
                $params['day'] = strtolower(trim($day));
            }

            $sql .= "
                ORDER BY
                    FIELD(LOWER(e.event_day), 'thursday','friday','saturday','sunday'),
                    e.start_time ASC
            ";

            $result = $this->db->prepare($sql);
            $result->execute($params);

            return $result->fetchAll(PDO::FETCH_ASSOC);
        } 
        catch (PDOException $e) 
        {
            throw new AppException('Unable to fetch stories. Please try again later.');
        }
    }

    /**
     * Get a single story by ID
     * @param int $storyId The ID of the story to retrieve
     * @return array|null Story record or null if not found
     * @throws AppException If database query fails
     */
    public function getStoryById(int $storyId): ?array
    {
        try {
            $result = $this->db->prepare("
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
                    e.event_day,
                    e.start_time,
                    e.end_time,
                    e.title AS event_title,
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
                WHERE s.story_id = :id
                LIMIT 1
            ");

            $result->execute(['id' => $storyId]);
            $row = $result->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } 
        catch (PDOException $e)
       {
            throw new AppException('Unable to fetch story data. Please try again later.');
        }
    }

    /**
     * Get all stories for admin panel
     * @return array Array of all story records
     * @throws AppException If database query fails
     */
    public function getAllStoriesForAdmin(): array
    {
        try {
            $result = $this->db->query("
                SELECT
                    story_id, name, slug, description,
                    image_path, story_type, age, language, template,
                    COALESCE(audience, '') AS audience,
                    event_id
                FROM stories
                ORDER BY story_id ASC
            ");

            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new AppException('Unable to fetch stories. Please try again later.');
        }
    }

    /**
     * Get story by slug
     * @param string $slug The slug of the story to retrieve
     * @return array|null Story record or null if not found
     * @throws AppException If database query fails
     */
    public function getStoryBySlug(string $slug): ?array
    {
        try {
            $result = $this->db->prepare("
                SELECT
                    story_id, name, slug, description,
                    image_path, story_type, age, language, template,
                    COALESCE(audience, '') AS audience,
                    event_id
                FROM stories
                WHERE slug = :slug
                LIMIT 1
            ");

            $result->execute(['slug' => trim($slug)]);
            $row = $result->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (PDOException $e) {
            throw new AppException('Unable to fetch story data. Please try again later.');
        }
    }

    /**
     * Create a new story
     * @param array $data Story data (name, slug, description, image_path, etc.)
     * @return int The ID of the newly created story
     * @throws AppException If database insert fails
     */
    public function createStory(array $data): int
    {
        try {
            $eventId = (int)($data['event_id'] ?? 0);
            $venueId = $this->getVenueIdForEvent($eventId);

            $result = $this->db->prepare("
                INSERT INTO stories
                    (name, slug, description, image_path, story_type, age, language, template, event_id, venue_id)
                VALUES
                    (:name, :slug, :description, :image_path, :story_type, :age, :language, :template, :event_id, :venue_id)
            ");

            $result->execute([
                'name'        => $data['name'],
                'slug'        => $data['slug'],
                'description' => $data['description'],
                'image_path'  => $data['image_path'],
                'story_type'  => $data['story_type'],
                'age'         => $data['age'],
                'language'    => $data['language'],
                'template'    => isset($data['template']) && $data['template'] !== '' ? $data['template'] : 'generic',
                'event_id'    => $eventId,
                'venue_id'    => $venueId,
            ]);

            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new AppException('Unable to create story. Please try again later.');
        }
    }


    /**
     * Update an existing story
     * @param int $storyId The ID of the story to update
     * @param array $data Story data to update
     * @return bool True if update successful, false otherwise
     * @throws AppException If database update fails
     */
    public function updateStory(int $storyId, array $data): bool
    {
        try {
            $eventId = (int)($data['event_id'] ?? 0);
            $venueId = $this->getVenueIdForEvent($eventId);

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
                    event_id    = :event_id,
                    venue_id    = :venue_id
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
                'template'    => isset($data['template']) && $data['template'] !== '' ? $data['template'] : 'generic',
                'event_id'    => $eventId,
                'venue_id'    => $venueId,
                'story_id'    => $storyId,
            ];

            $result = $this->db->prepare($sql);
            return $result->execute($params);
        } catch (PDOException $e) {
            throw new AppException('Unable to update story. Please try again later.');
        }
    }

    /**
     * Delete a story
     * @param int $storyId The ID of the story to delete
     * @return bool True if deletion successful, false otherwise
     * @throws AppException If database delete fails
     */
    public function deleteStory(int $storyId): bool
    {
        try {
            $result = $this->db->prepare("DELETE FROM stories WHERE story_id = :story_id");
            return $result->execute(['story_id' => $storyId]);
        } catch (PDOException $e) {
            throw new AppException('Unable to delete story. Please try again later.');
        }
    }

    /**
     * Get detail page by story ID
     * @param int $storyId The ID of the story to get detail page for
     * @return array|null Detail page record or null if not found
     * @throws AppException If database query fails
     */
    public function getDetailPageByStoryId(int $storyId): ?array
    {
        try {
            $result = $this->db->prepare("
                SELECT * FROM story_detail_pages
                WHERE story_id = :story_id
                LIMIT 1
            ");

            $result->execute(['story_id' => $storyId]);
            $row = $result->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (PDOException $e) {
            throw new AppException('Unable to fetch story detail page. Please try again later.');
        }
    }

    /**
     * Check if story has a detail page
     * @param int $storyId The ID of the story to check
     * @return bool True if detail page exists, false otherwise
     * @throws AppException If database query fails
     */
    public function hasDetailPage(int $storyId): bool
    {
        try {
            $result = $this->db->prepare("
                SELECT 1 FROM story_detail_pages
                WHERE story_id = :story_id
                LIMIT 1
            ");

            $result->execute(['story_id' => $storyId]);
            return (bool)$result->fetchColumn();
        } catch (PDOException $e) {
            throw new AppException('Unable to check story detail page. Please try again later.');
        }
    }

    /**
     * Save or update story detail page
     * @param int $storyId The ID of the story
     * @param array $data Detail page data with fields like hero_image, article_*, highlights, gallery
     * @return bool True if save successful, false otherwise
     * @throws AppException If database operation fails
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
            } 
            else 
            {
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

            $result = $this->db->prepare($sql);

            return $result->execute([
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
        catch (PDOException $e) 
        {
            throw new AppException('Unable to save story detail page. Please try again later.');
        }
    }

    private function getVenueIdForEvent(int $eventId): int
    {
        if ($eventId <= 0) {
            return 0;
        }

        $result = $this->db->prepare("SELECT venue_id FROM events WHERE event_id = :event_id LIMIT 1");
        $result->execute(['event_id' => $eventId]);

        return (int)$result->fetchColumn();
    }
}
