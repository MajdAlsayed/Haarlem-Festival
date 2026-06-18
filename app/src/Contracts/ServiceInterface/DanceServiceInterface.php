<?php

namespace App\Contracts\ServiceInterface;

use App\Models\Event;
use App\ViewModels\DanceViewModel;

interface DanceServiceInterface
{
    public function buildIndexViewModel(): DanceViewModel;

    public function getDancePageData(): array;

    public function getEventsGroupedByDay(): array;

    public function getArtistsOrdered(): array;

    public function getDanceSettings(): array;
}
