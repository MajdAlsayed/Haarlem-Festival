<?php

namespace App\Services;

use App\Contracts\HistoryRepositoryInterface;
use App\Models\HistoryLocation;
use App\Models\HistoryImage;

class HistoryService
{
    public function __construct(
        private HistoryRepositoryInterface $historyRepository
    ) {
    }

    public function getAllLocations(): array
    {
        return $this->historyRepository->getAllLocations();
    }
    public function getLocationById(int $id): ?HistoryLocation
    {
        return $this->historyRepository->getLocationById($id);
    }
    public function getPrimaryImage(int $locationId): ?HistoryImage
    {
        return $this->historyRepository->getPrimaryImage($locationId);
    }
    public function getPageBlocks(string $slug): array
    {
        return $this->historyRepository->getPageBlocks($slug);
    }
    public function getPageHeroImage(int $pageId): ?HistoryImage
    {
        return $this->historyRepository->getPageHeroImage($pageId);
    }
}