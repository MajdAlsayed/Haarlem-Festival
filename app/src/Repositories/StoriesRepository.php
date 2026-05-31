<?php

namespace App\Repositories;

use App\Contracts\IStoriesRepository;
use App\Core\Repository;
use App\Exceptions\AppException;
use PDO;
use PDOException;

class StoriesRepository extends Repository implements IStoriesRepository
{
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
                    s.venue_id,
                    s.event_id,
                    e.event_day,
                    e.start_time,
                    e.end_time,
                    e.title AS event_title
                FROM stories s
                LEFT JOIN events e ON e.event_id = s.event_id
            ";

            $params = [];

            if (!empty($day) && strtolower(trim($day)) !== 'all') {
                $sql .= " WHERE LOWER(TRIM(e.event_day)) = :day";
                $params['day'] = strtolower(trim($day));
            }

            $sql .= "
                ORDER BY
                    FIELD(LOWER(e.event_day), 'thursday','friday','saturday','sunday'),
                    e.start_time ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new AppException('Unable to fetch stories. Please try again later.');
        }
    }

    public function getStoryById(int $storyId): ?array
    {
        try {
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
                    COALESCE(s.audience, '') AS audience,
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

            return $row ?: null;
        } catch (PDOException $e) {
            throw new AppException('Unable to fetch story data. Please try again later.');
        }
    }

    public function getAllStoriesForAdmin(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT
                    story_id, name, slug, description,
                    image_path, story_type, age, language, template,
                    COALESCE(audience, '') AS audience,
                    venue_id, event_id
                FROM stories
                ORDER BY story_id ASC
            ");

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new AppException('Unable to fetch stories. Please try again later.');
        }
    }

    public function getStoryBySlug(string $slug): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    story_id, name, slug, description,
                    image_path, story_type, age, language, template,
                    COALESCE(audience, '') AS audience,
                    venue_id, event_id
                FROM stories
                WHERE slug = :slug
                LIMIT 1
            ");

            $stmt->execute(['slug' => trim($slug)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (PDOException $e) {
            throw new AppException('Unable to fetch story data. Please try again later.');
        }
    }

    public function createStory(array $data): int
    {
        try {
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
        } catch (PDOException $e) {
            throw new AppException('Unable to create story. Please try again later.');
        }
    }


    public function updateStory(int $storyId, array $data): bool
    {
        try {
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
                'template'    => isset($data['template']) && $data['template'] !== '' ? $data['template'] : 'generic',
                'event_id'    => (int)($data['event_id'] ?? 0),
                'story_id'    => $storyId,
            ];

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new AppException('Unable to update story. Please try again later.');
        }
    }

    public function deleteStory(int $storyId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM stories WHERE story_id = :story_id");
            return $stmt->execute(['story_id' => $storyId]);
        } catch (PDOException $e) {
            throw new AppException('Unable to delete story. Please try again later.');
        }
    }

    public function getDetailPageByStoryId(int $storyId): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM story_detail_pages
                WHERE story_id = :story_id
                LIMIT 1
            ");

            $stmt->execute(['story_id' => $storyId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (PDOException $e) {
            throw new AppException('Unable to fetch story detail page. Please try again later.');
        }
    }

    public function hasDetailPage(int $storyId): bool
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 1 FROM story_detail_pages
                WHERE story_id = :story_id
                LIMIT 1
            ");

            $stmt->execute(['story_id' => $storyId]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new AppException('Unable to check story detail page. Please try again later.');
        }
    }

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
        } catch (PDOException $e) {
            throw new AppException('Unable to save story detail page. Please try again later.');
        }
    }
}