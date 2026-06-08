<?php

namespace App\Services;

use App\Contracts\IStoriesRepository;
use App\Contracts\ServiceInterface\StoriesServiceInterface;
use App\Exceptions\NotFoundException;

class StoriesService implements StoriesServiceInterface
{
    /**
     * @var IStoriesRepository Repository dependency for data access
     */
    public function __construct(private IStoriesRepository $repo)
    {
    }


    /**
     * Get stories data for home page display
     * @param string|null $day Optional day filter (all, thursday, friday, saturday, sunday)
     * @return array Associative array with 'stories' and 'schedule' keys
     */
    public function getStoriesHomeData(?string $day = null): array
    {
        $stories = $this->repo->getStories($day);

        return [
            'stories'  => $stories,
            'schedule' => $this->buildSchedule($stories),
        ];
    }

    /**
     * @param int $storyId The ID of the story to retrieve
     * @return array Associative array with 'story' and 'detailPage' keys
     * @throws NotFoundException If story is not found
     */
    public function getStoryDetailData(int $storyId): array
    {
        $story = $this->repo->getStoryById($storyId);

        if (!$story) {
            throw new NotFoundException('Story not found.');
        }

        return [
            'story'      => $story,
            'detailPage' => $this->repo->getDetailPageByStoryId($storyId),
        ];
    }



    /**
     * Get all stories for admin listing
     * @return array Array of all story records
     */
    public function getAllStoriesForAdmin(): array
    {
        return $this->repo->getAllStoriesForAdmin();
    }

    /**
     * Get story data for editing
     * @param int $storyId The ID of the story to retrieve
     * @return array|null Story record or null if not found
     */
    public function getStoryForEdit(int $storyId): ?array
    {
        return $this->repo->getStoryById($storyId);
    }

    /**
     * Create a new story
     * @param array $data Story data (name, slug, description, image_path, etc.)
     * @return int The ID of the newly created story
     */
    public function createStory(array $data): int
    {
        return $this->repo->createStory($data);
    }

    /**
     * Update an existing story
     * @param int $storyId The ID of the story to update
     * @param array $data Story data to update
     * @return bool True if update successful, false otherwise
     */
    public function updateStory(int $storyId, array $data): bool
    {
        return $this->repo->updateStory($storyId, $data);
    }

    /**
     * Delete a story
     * @param int $storyId The ID of the story to delete
     * @return bool True if deletion successful, false otherwise
     */
    public function deleteStory(int $storyId): bool
    {
        return $this->repo->deleteStory($storyId);
    }

    /**
     * Check if story has a detail page
     * @param int $storyId The ID of the story to check
     * @return bool True if detail page exists, false otherwise
     */
    public function hasDetailPage(int $storyId): bool
    {
        return $this->repo->hasDetailPage($storyId);
    }


    /**
     * Get story detail page for CMS editing
     * @param string $slug The slug of the story to retrieve
     * @return array Associative array with 'story' and 'detailPage' keys
     * @throws NotFoundException If story with slug not found
     */
    public function getDetailPageForCms(string $slug): array
    {
        $story = $this->repo->getStoryBySlug($slug);

        if (!$story) {
            throw new NotFoundException('Story not found.');
        }

        $detailPage = $this->repo->getDetailPageByStoryId((int)$story['story_id']);

        return [
            'story'      => $story,
            'detailPage' => $detailPage,
        ];
    }

    /**
     * Save or update story detail page
     * @param int $storyId The ID of the story
     * @param array $data Detail page data (hero_image, article_*, highlights, gallery, etc.)
     * @return bool True if save successful, false otherwise
     */
    public function saveDetailPage(int $storyId, array $data): bool
    {
        $highlights = isset($data['highlights']) && is_array($data['highlights'])
            ? $data['highlights']
            : [];

        $gallery = isset($data['gallery']) && is_array($data['gallery'])
            ? $data['gallery']
            : [];

        $payload = [
            'hero_image'            => isset($data['hero_image'])            && $data['hero_image']            !== '' ? $data['hero_image']            : null,
            'hero_heading'          => isset($data['hero_heading'])          && $data['hero_heading']          !== '' ? $data['hero_heading']          : null,
            'hero_description'      => isset($data['hero_description'])      && $data['hero_description']      !== '' ? $data['hero_description']      : null,
            'article_title'         => isset($data['article_title'])         && $data['article_title']         !== '' ? $data['article_title']         : null,
            'article_image'         => isset($data['article_image'])         && $data['article_image']         !== '' ? $data['article_image']         : null,
            'article_image_caption' => isset($data['article_image_caption']) && $data['article_image_caption'] !== '' ? $data['article_image_caption'] : null,
            'article_paragraph_1'   => isset($data['article_paragraph_1'])   && $data['article_paragraph_1']   !== '' ? $data['article_paragraph_1']   : null,
            'article_paragraph_2'   => isset($data['article_paragraph_2'])   && $data['article_paragraph_2']   !== '' ? $data['article_paragraph_2']   : null,
            'article_paragraph_3'   => isset($data['article_paragraph_3'])   && $data['article_paragraph_3']   !== '' ? $data['article_paragraph_3']   : null,
            'highlights'            => json_encode($highlights, JSON_UNESCAPED_UNICODE),
            'gallery'               => json_encode($gallery,    JSON_UNESCAPED_UNICODE),
        ];

        return $this->repo->saveDetailPage($storyId, $payload);
    }


    /**
     * Build schedule structure from stories
     *
     * @param array $stories Array of story records with language and event_day fields
     * @return array Schedule organized by language and day
     */
    private function buildSchedule(array $stories): array
    {
        $out = ['NL' => [], 'ENG' => []];

        foreach ($stories as $story) {
            
            $lang = strtoupper(trim((string)($story['language'] ?? '')));
            $day  = strtolower(trim((string)($story['event_day'] ?? '')));

           
            if ($lang === '' || $day === '') {
                continue;
            }

           
            if (!isset($out[$lang])) {
                $out[$lang] = [];
            }

            $start = isset($story['start_time']) ? trim((string)$story['start_time']) : '';
            $end   = isset($story['end_time'])   ? trim((string)$story['end_time'])   : '';

            $time = $start;
            if ($start !== '' && $end !== '') {
                $time = $start . '–' . $end;
            }

            $out[$lang][$day][] = [
                'time'  => $time,
                'title' => (string)($story['story_name'] ?? $story['name'] ?? $story['title'] ?? ''),
                'age'   => (string)($story['age'] ?? ''),
            ];
        }

        return $out;
    }
}