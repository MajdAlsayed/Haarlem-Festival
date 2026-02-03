<?php

namespace App\Controllers;

use App\Repositories\EventRepository;

class DanceController
{
    private EventRepository $eventRepository;

    public function __construct()
    {
        $this->eventRepository = new EventRepository();
    }

    public function index(): void
    {
        $events = $this->eventRepository->getByCategory('dance');

        require __DIR__ . '/../Views/Dance/Index.php';
    }
}
