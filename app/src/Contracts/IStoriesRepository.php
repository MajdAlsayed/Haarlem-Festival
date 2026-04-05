<?php

namespace App\Contracts;

interface IStoriesRepository
{
    // Public/frontend
    public function getStories(?string $day = null): array;
    public function getStoriesByVenue(int $venueId, ?string $day = null): array;
    public function getStoryById(int $storyId): ?array;
    public function getVenueBySlug(string $slug): ?array;

    // CMS - story CRUD
    public function getAllStoriesForAdmin(): array;
    public function getStoryBySlug(string $slug): ?array;
    public function createStory(array $data): int;
    public function updateStory(int $storyId, array $data): bool;
    public function deleteStory(int $storyId): bool;

    // CMS - custom detail page
    public function getDetailPageByStoryId(int $storyId): ?array;
    public function saveDetailPage(int $storyId, array $data): bool;
    public function hasDetailPage(int $storyId): bool;
}