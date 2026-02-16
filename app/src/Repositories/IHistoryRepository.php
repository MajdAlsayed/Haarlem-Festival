<?php

namespace App\Repositories;

use App\Models\HistoryTour;
use App\Models\HistoryLocation;
use App\Models\HistoryImage;

interface IHistoryRepository
{
    // TOURS
    public function getAllTours(): array;
    public function getTourById(int $id): ?HistoryTour;
    public function getToursByDate(string $date): array;

// LOCATIONS
    public function getAllLocations(): array;

// IMAGES
    public function getPrimaryImage(int $locationId): ?HistoryImage;
    public function getLocationImages(int $locationId): array;
}