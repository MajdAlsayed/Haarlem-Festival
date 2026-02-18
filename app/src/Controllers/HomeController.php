<?php

namespace App\Controllers;

use App\Exceptions\NotFoundException;
use App\Repositories\EventRepository;
use App\Repositories\EventTypeRepository;
use App\Repositories\PageRepository;
use App\Services\EventService;
use App\Services\PageService;
use App\ViewModels\HomeViewModel;

class HomeController
{
    private PageService $pageService;
    private EventService $eventService;
    private EventTypeRepository $eventTypeRepository;

    public function __construct()
    {
        $this->pageService = new PageService(new PageRepository());
        $this->eventService = new EventService(new EventRepository());
        $this->eventTypeRepository = new EventTypeRepository();
    }

    public function index(): void
    {
        $page = $this->pageService->getBySlug('home');
        if ($page === null) {
            throw new NotFoundException('Homepage not found');
        }

        $allEvents = $this->eventService->getAll();
        $events = $this->pickOneEventPerCategory($allEvents);

        $viewModel = new HomeViewModel($page, $events);

        require __DIR__ . '/../Views/Home/Index.php';
    }

    /**
     * @param \App\Models\Event[] $allEvents
     * @return \App\Models\Event[]
     */
    private function pickOneEventPerCategory(array $allEvents): array
    {
        $byType = [];
        foreach ($allEvents as $event) {
            $id = $event->eventTypeId;
            if (!isset($byType[$id])) {
                $byType[$id] = $event;
            }
        }
        $ordered = [];
        $typeIds = $this->eventTypeRepository->getAllIdsOrdered();
        foreach ($typeIds as $eventTypeId) {
            if (isset($byType[$eventTypeId])) {
                $ordered[] = $byType[$eventTypeId];
            }
        }
        return $ordered;
    }
}
