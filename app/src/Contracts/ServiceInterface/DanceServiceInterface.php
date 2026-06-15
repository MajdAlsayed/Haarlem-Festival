<?php

namespace App\Contracts\ServiceInterface;

use App\Models\Event;
use App\ViewModels\DanceViewModel;

interface DanceServiceInterface
{
    public function buildIndexViewModel(): DanceViewModel;

    /** @return array<string, Event[]> */
    public function getEventsGroupedByDay(): array;

    public function getArtistsOrdered(): array;

    public function getDanceSettings(): array;
}
