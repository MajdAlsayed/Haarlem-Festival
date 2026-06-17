<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\IStoriesRepository;
use App\Contracts\ServiceInterface\StoriesServiceInterface;
use App\Exceptions\NotFoundException;
use App\Validation\StoryValidator;

class StoriesService implements StoriesServiceInterface
{
    private const ALLOWED_DAYS = ['all', 'thursday', 'friday', 'saturday', 'sunday'];

    private IStoriesRepository $repo;
    private StoriesAdminUploadService $uploader;

    public function __construct(IStoriesRepository $repo)
    {
        $this->repo = $repo;
        $this->uploader = new StoriesAdminUploadService();
    }

    // Public story page methods.

    public function getStoriesHomePageData(): array
    {
        $stories = $this->repo->getStories('all');

        return [
            'featured' => array_slice($stories, 0, 3),
        ];
    }

    public function getStoriesEventsPageData(string $day): array
    {
        return [
            'selectedDay' => $this->normalizeDay($day),
        ];
    }

    /**
     * Get stories for the public listing, optionally filtered by day.
     */
    public function getStoriesHomeData(?string $day = null): array
    {
        return [
            'stories' => $this->repo->getStories($this->normalizeDay((string)($day ?? 'all'))),
        ];
    }

    public function getStoriesApiData(string $day): array
    {
        $day = $this->normalizeDay($day);
        $stories = $this->repo->getStories($day);
        $output = [];

        foreach ($stories as $story) {
            $output[] = $this->formatStoryForApi($story);
        }

        return [
            'success' => true,
            'day' => $day,
            'count' => count($output),
            'stories' => $output,
        ];
    }

    /**
     * Get one public story detail page.
     *
     * @throws NotFoundException If the story does not exist.
     */
    public function getStoryDetailData(int $storyId): array
    {
        $story = $this->repo->getStoryById($storyId);

        if (!$story) {
            throw new NotFoundException('Story not found.');
        }

        return [
            'story' => $story,
            'detailPage' => $this->repo->getDetailPageByStoryId($storyId),
        ];
    }

    // Admin/CMS methods.

    /**
     * Get all stories for the admin listing.
     */
    public function getAllStoriesForAdmin(): array
    {
        return $this->repo->getAllStoriesForAdmin();
    }

    public function getAllStoriesForAdminWithDetailStatus(): array
    {
        $stories = $this->repo->getAllStoriesForAdmin();

        foreach ($stories as &$story) {
            $story['has_detail_page'] = $this->hasDetailPage((int)($story['story_id'] ?? 0));
        }
        unset($story);

        return $stories;
    }

    /**
     * Get a story record for the admin edit form.
     */
    public function getStoryForEdit(int $storyId): ?array
    {
        return $this->repo->getStoryById($storyId);
    }

    /**
     * Create a story from validated CMS form data.
     */
    public function createStory(array $data): int
    {
        return $this->repo->createStory($data);
    }

    /**
     * Update a story from validated CMS form data.
     */
    public function updateStory(int $storyId, array $data): bool
    {
        return $this->repo->updateStory($storyId, $data);
    }

    /**
     * Delete a story from the CMS.
     */
    public function deleteStory(int $storyId): bool
    {
        return $this->repo->deleteStory($storyId);
    }

    /**
     * Check whether a story already has custom detail-page content.
     */
    public function hasDetailPage(int $storyId): bool
    {
        return $this->repo->hasDetailPage($storyId);
    }

    /**
     * Load story and detail-page data for the CMS detail-page editor.
     *
     * @throws NotFoundException If the story does not exist.
     */
    public function getDetailPageForCms(string $slug): array
    {
        $story = $this->repo->getStoryBySlug($slug);

        if (!$story) {
            throw new NotFoundException('Story not found.');
        }

        return [
            'story' => $story,
            'detailPage' => $this->repo->getDetailPageByStoryId((int)$story['story_id']),
        ];
    }

    public function getDetailPageFormData(string $slug): array
    {
        $data = $this->getDetailPageForCms($slug);
        $detailPage = is_array($data['detailPage'] ?? null) ? $data['detailPage'] : [];

        return [
            'story' => $data['story'],
            'detailPage' => $detailPage,
            'highlights' => $this->decodeJsonArray($detailPage['highlights'] ?? '[]'),
            'gallery' => $this->decodeJsonArray($detailPage['gallery'] ?? '[]'),
        ];
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     * @return array<string, mixed>
     */
    public function prepareStoryData(array $post, array $files): array
    {
        $uploadedImage = $this->getUploader()->storeStoryImage(
            isset($files['image_upload']) && is_array($files['image_upload']) ? $files['image_upload'] : null,
            'story'
        );
        $imagePath = $uploadedImage !== null ? $uploadedImage : trim((string)($post['image_path'] ?? ''));

        return [
            'name' => trim((string)($post['name'] ?? '')),
            'slug' => trim((string)($post['slug'] ?? '')),
            'description' => trim((string)($post['description'] ?? '')),
            'image_path' => $imagePath,
            'story_type' => trim((string)($post['story_type'] ?? '')),
            'age' => trim((string)($post['age'] ?? '')),
            'language' => trim((string)($post['language'] ?? '')),
            'template' => trim((string)($post['template'] ?? 'generic')),
            'audience' => trim((string)($post['audience'] ?? '')),
            'event_id' => (int)($post['event_id'] ?? 0),
            'event_day' => trim((string)($post['event_day'] ?? '')),
            'start_time' => trim((string)($post['start_time'] ?? '')),
            'end_time' => trim((string)($post['end_time'] ?? '')),
        ];
    }

    public function saveStoryFromForm(int $storyId, array $data, bool $isCreate): void
    {
        (new StoryValidator())->validateStory($data);

        if ($isCreate) {
            $this->createStory($data);
            return;
        }

        $this->updateStory($storyId, $data);
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     * @return array<string, mixed>
     */
    public function prepareDetailPageData(array $post, array $files): array
    {
        $uploadedHeroImage = $this->getUploader()->storeStoryImage(
            isset($files['hero_image_upload']) && is_array($files['hero_image_upload']) ? $files['hero_image_upload'] : null,
            'detail-hero'
        );
        $heroImage = $uploadedHeroImage !== null ? $uploadedHeroImage : trim((string)($post['hero_image'] ?? ''));

        $uploadedArticleImage = $this->getUploader()->storeStoryImage(
            isset($files['article_image_upload']) && is_array($files['article_image_upload']) ? $files['article_image_upload'] : null,
            'detail-article'
        );
        $articleImage = $uploadedArticleImage !== null ? $uploadedArticleImage : trim((string)($post['article_image'] ?? ''));

        return [
            'hero_image' => $heroImage,
            'hero_heading' => trim((string)($post['hero_heading'] ?? '')),
            'hero_description' => trim((string)($post['hero_description'] ?? '')),
            'article_title' => trim((string)($post['article_title'] ?? '')),
            'article_image' => $articleImage,
            'article_image_caption' => trim((string)($post['article_image_caption'] ?? '')),
            'article_paragraph_1' => trim((string)($post['article_paragraph_1'] ?? '')),
            'article_paragraph_2' => trim((string)($post['article_paragraph_2'] ?? '')),
            'article_paragraph_3' => trim((string)($post['article_paragraph_3'] ?? '')),
            'highlights' => $post['highlights'] ?? [],
            'gallery' => $post['gallery'] ?? [],
        ];
    }

    /**
     * Save normalized detail-page fields from the CMS editor.
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
            'hero_image' => $this->nullableString($data, 'hero_image'),
            'hero_heading' => $this->nullableString($data, 'hero_heading'),
            'hero_description' => $this->nullableString($data, 'hero_description'),
            'article_title' => $this->nullableString($data, 'article_title'),
            'article_image' => $this->nullableString($data, 'article_image'),
            'article_image_caption' => $this->nullableString($data, 'article_image_caption'),
            'article_paragraph_1' => $this->nullableString($data, 'article_paragraph_1'),
            'article_paragraph_2' => $this->nullableString($data, 'article_paragraph_2'),
            'article_paragraph_3' => $this->nullableString($data, 'article_paragraph_3'),
            'highlights' => json_encode($highlights, JSON_UNESCAPED_UNICODE) ?: '[]',
            'gallery' => json_encode($gallery, JSON_UNESCAPED_UNICODE) ?: '[]',
        ];

        return $this->repo->saveDetailPage($storyId, $payload);
    }

    // Private helpers.

    public function normalizeDay(string $input): string
    {
        $day = strtolower(trim($input));

        return in_array($day, self::ALLOWED_DAYS, true) ? $day : 'all';
    }

    private function nullableString(array $data, string $key): ?string
    {
        $value = trim((string)($data[$key] ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * @return array<int, mixed>
     */
    private function decodeJsonArray($value): array
    {
        $decoded = json_decode((string)$value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function formatStoryForApi(array $story): array
    {
        return [
            'story_id' => (int)($story['story_id'] ?? 0),
            'ticket_details_id' => (int)($story['ticket_details_id'] ?? 0),
            'name' => $story['story_name'] ?? $story['name'] ?? '',
            'description' => $story['description'] ?? '',
            'image_path' => $story['image_path'] ?? '',
            'story_type' => $story['story_type'] ?? '',
            'age' => $story['age'] ?? '',
            'language' => $story['language'] ?? '',
            'template' => $story['template'] ?? 'generic',
            'audience' => $story['audience'] ?? '',
            'event_day' => $story['event_day'] ?? '',
            'start_time' => $story['start_time'] ?? '',
        ];
    }

    private function getUploader(): StoriesAdminUploadService
    {
        return $this->uploader;
    }
}
