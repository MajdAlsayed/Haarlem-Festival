<?php

namespace App\Controllers;

use App\Exceptions\NotFoundException;
use App\Repositories\EventRepository;
use App\Repositories\FoodSettingsRepository;
use App\Repositories\RestaurantRepository;
use App\Services\EventService;
use App\ViewModels\FoodViewModel;

class FoodController
{
    private EventService $eventService;
    private FoodSettingsRepository $foodSettingsRepository;
    private RestaurantRepository $restaurantRepository;

    public function __construct()
    {
        $this->eventService = new EventService(new EventRepository());
        $this->foodSettingsRepository = new FoodSettingsRepository();
        $this->restaurantRepository = new RestaurantRepository();
    }

    public function index(): void
{
    $events = $this->eventService->getByCategory('yammy');
    $foodSettings = $this->foodSettingsRepository->getAll();
    $restaurants = $this->restaurantRepository->getAll();

    $viewModel = new FoodViewModel($events, $foodSettings, $restaurants);

    require __DIR__ . '/../Views/Food/Index.php';
}

    public function restaurant(int $id): void
    {
        $restaurant = $this->restaurantRepository->getById($id);

        if (!$restaurant) {
            throw new NotFoundException('Restaurant not found');
        }

        require __DIR__ . '/../Views/Food/Restaurant.php';
    }
}