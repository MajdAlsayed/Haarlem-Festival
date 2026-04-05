<?php

namespace App\Contracts;

use App\Models\HistoryImage;
use App\Models\HistoryTour;
use App\Models\HistoryLocation;

interface HistoryRepositoryInterface
{
    // TOURS
    public function getAllTours(): array;

    public function getTourById(int $id): ?HistoryTour;

    public function getToursByDate(string $date): array;

    public function getToursWithDetailsByDate(string $date): array;
    public function getTourDates(): array;

    // LOCATIONS
    public function getAllLocations(): array;

    public function getLocationById(int $id): ?HistoryLocation;

    public function getLocationBySlug(string $slug): ?HistoryLocation;

    public function getLocationBySortOrder(int $sortOrder): ?HistoryLocation;

    // IMAGES
    public function getPrimaryImage(int $locationId): ?HistoryImage;

    public function getLocationImages(int $locationId): array;

    public function getPageHeroImage(int $pageId): ?HistoryImage;

    public function getEventHeroImage(int $eventId): ?HistoryImage;

    public function getLocationGallery(int $locationId): array;

    public function getImageById(int $imageId): ?HistoryImage;

    // BLOCKS

    /** @return array{page_id: int|null, blocks: array} */
    public function getPageBlocks(string $slug): array;

    /** @return array{page_id: int|null, blocks: list<array>} */
    public function getPageBlocksList(string $slug): array;
}