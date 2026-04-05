<?php

namespace App\Contracts\ServiceInterface;

interface StoriesServiceInterface
{
    // Public/frontend
    public function getStoriesHomeData(?string $day = null): array;
    public function getVenuePageData(string $slug, ?string $day = null): array;
    public function getStoryDetailData(int $storyId): array;

    // CMS - story CRUD
    public function getAllStoriesForAdmin(): array;
    public function getStoryForEdit(int $storyId): ?array;
    public function createStory(array $data): int;
    public function updateStory(int $storyId, array $data): bool;
    public function deleteStory(int $storyId): bool;

    // CMS - detail page
    public function getDetailPageForCms(string $slug): array;
    public function saveDetailPage(int $storyId, array $data): bool;
    public function hasDetailPage(int $storyId): bool;
}