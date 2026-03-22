<?php

namespace App\Services;

use App\Contracts\HistoryRepositoryInterface;
use App\Contracts\ServiceInterface\HistoryServiceInterface;
use App\Models\HistoryLocation;
use App\Models\HistoryImage;
use App\Models\HistoryTour;

class HistoryService implements HistoryServiceInterface
{
    public function __construct(
        private HistoryRepositoryInterface $historyRepository
    )
    {
    }

    // LOCATIONS
    public function getAllLocations(): array
    {
        return $this->historyRepository->getAllLocations();
    }

    public function getLocationById(int $id): ?HistoryLocation
    {
        return $this->historyRepository->getLocationById($id);
    }

    public function getLocationBySlug(string $slug): ?HistoryLocation
    {
        return $this->historyRepository->getLocationBySlug($slug);
    }

    public function getLocationBySortOrder(int $sortOrder): ?HistoryLocation
    {
        return $this->historyRepository->getLocationBySortOrder($sortOrder);
    }

    //IMAGES
    public function getPrimaryImage(int $locationId): ?HistoryImage
    {
        return $this->historyRepository->getPrimaryImage($locationId);
    }

    public function getPageHeroImage(int $pageId): ?HistoryImage
    {
        return $this->historyRepository->getPageHeroImage($pageId);
    }

    public function getLocationGallery(int $locationId): array
    {
        return $this->historyRepository->getLocationGallery($locationId);
    }

    public function getImageById(int $imageId): ?HistoryImage
    {
        return $this->historyRepository->getImageById($imageId);
    }

    // BLOCKS
    public function getPageBlocks(string $slug): array
    {
        return $this->historyRepository->getPageBlocks($slug);
    }

    public function getPageBlocksList(string $slug): array
    {
        return $this->historyRepository->getPageBlocksList($slug);
    }

    // TOURS
    public function getToursWithDetailsByDate(string $date): array
    {
        return $this->historyRepository->getToursWithDetailsByDate($date);

    }

    public function getTourDates(): array
    {
        return $this->historyRepository->getTourDates();
    }


}