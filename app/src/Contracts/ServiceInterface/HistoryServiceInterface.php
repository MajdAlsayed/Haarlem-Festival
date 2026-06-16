<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

use App\Models\HistoryImage;
use App\Models\HistoryLocation;

interface HistoryServiceInterface
{
    // LOCATIONS
    public function getAllLocations(): array;

    public function getLocationById(int $id): ?HistoryLocation;

    public function getLocationBySlug(string $slug): ?HistoryLocation;

    public function getLocationBySortOrder(int $sortOrder): ?HistoryLocation;

    public function getLocationsWithImages(array $blocks): array;

    // IMAGES
    public function getAllImages(): array;

    public function getPrimaryImage(int $locationId): ?HistoryImage;

    public function getPageHeroImage(int $pageId): ?HistoryImage;

    public function getLocationGallery(int $locationId): array;

    public function getImageById(int $imageId): ?HistoryImage;

    public function insertImage(string $imageUrl, string $altText): int;

    // BLOCKS
    public function getPageBlocks(string $slug): array;

    public function getPageBlocksList(string $slug): array;

    public function updatePageBlock(int $blockId, array $content): bool;

    // TOURS
    public function getToursWithDetailsByDate(string $date): array;

    public function getTourDates(): array;
}