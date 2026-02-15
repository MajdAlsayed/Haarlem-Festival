<?php

namespace App\Repositories;

use App\Models\HistoryTour;

interface IHistoryRepository
{
public function getAllTours(): array;
public function getTourById(int $id) : ?HistoryTour;
public function getToursByDate(string $date): array;
}