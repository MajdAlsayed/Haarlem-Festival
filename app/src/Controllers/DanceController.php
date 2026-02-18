<?php

namespace App\Controllers;

use App\Repositories\EventRepository;
use App\Services\EventService;
use App\ViewModels\DanceViewModel;

class DanceController
{
    private EventService $eventService;

    public function __construct()
    {
        $this->eventService = new EventService(new EventRepository());
    }

    public function index(): void
    {
        $events = $this->eventService->getByCategory('dance');
        $fridayEvents = $this->eventService->getByCategoryAndDay('dance', 'friday');
        $saturdayEvents = $this->eventService->getByCategoryAndDay('dance', 'saturday');
        $sundayEvents = $this->eventService->getByCategoryAndDay('dance', 'sunday');

        $viewModel = new DanceViewModel($events, $fridayEvents, $saturdayEvents, $sundayEvents);

        require __DIR__ . '/../Views/Dance/Index.php';
    }
}
