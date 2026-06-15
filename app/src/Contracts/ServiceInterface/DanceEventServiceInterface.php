<?php

namespace App\Contracts\ServiceInterface;

use App\ViewModels\EventDetailViewModel;

interface DanceEventServiceInterface
{
    public function buildDetailViewModel(int $eventId): EventDetailViewModel;
}
