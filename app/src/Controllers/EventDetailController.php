<?php

namespace App\Controllers;

use App\Contracts\ServiceInterface\EventServiceInterface;
use App\Exceptions\NotFoundException;
use App\Repositories\EventRepository;
use App\Services\EventService;
use App\ViewModels\EventDetailViewModel;

class EventDetailController
{
    private EventServiceInterface $eventService;

    public function __construct()
    {
        $this->eventService = new EventService(new EventRepository());
    }

    public function show(int $eventId): void
    {
        $event = $this->eventService->getById($eventId);
        if (!$event) {
            throw new NotFoundException('Event not found');
        }
        $viewModel = new EventDetailViewModel($event);
        require __DIR__ . '/../Views/Dance/EventDetail.php';
    }
}
