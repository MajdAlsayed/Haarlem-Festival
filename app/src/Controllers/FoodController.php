<?php

namespace App\Controllers;

use App\Repositories\EventRepository;
use App\Repositories\FoodSettingsRepository;
use App\Services\EventService;
use App\ViewModels\FoodViewModel;

class FoodController
{
    private EventService $eventService;
    private FoodSettingsRepository $foodSettingsRepository;

    public function __construct()
    {
        $this->eventService = new EventService(new EventRepository());
        $this->foodSettingsRepository = new FoodSettingsRepository();
    }

    public function index(): void
    {
        $events = $this->eventService->getByCategory('yammy');
        $foodSettings = $this->foodSettingsRepository->getAll();

        $viewModel = new FoodViewModel($events, $foodSettings);

        require __DIR__ . '/../Views/Food/Index.php';
    }
}