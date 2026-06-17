<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

interface StoriesServiceInterface
{
    // Public/frontend
    public function getStoriesHomePageData(): array;
    public function getStoriesEventsPageData(string $day): array;
    public function getStoriesHomeData(?string $day = null): array;
    public function getStoriesApiData(string $day): array;
    public function getStoryDetailData(int $storyId): array;
    public function normalizeDay(string $input): string;

    // CMS - story CRUD
    public function getAllStoriesForAdmin(): array;
    public function getAllStoriesForAdminWithDetailStatus(): array;
    public function getStoryForEdit(int $storyId): ?array;
    public function prepareStoryData(array $post, array $files): array;
    public function saveStoryFromForm(int $storyId, array $data, bool $isCreate): void;
    public function createStory(array $data): int;
    public function updateStory(int $storyId, array $data): bool;
    public function deleteStory(int $storyId): bool;

    // CMS - detail page
    public function getDetailPageForCms(string $slug): array;
    public function getDetailPageFormData(string $slug): array;
    public function prepareDetailPageData(array $post, array $files): array;
    public function saveDetailPage(int $storyId, array $data): bool;
    public function hasDetailPage(int $storyId): bool;
}
