<?php

namespace App\Controllers;

use App\Exceptions\NotFoundException;
use App\Repositories\FoodSettingsRepository;
use App\Repositories\RestaurantRepository;
use App\Repositories\SettingsRepository;
use App\ViewModels\FoodViewModel;

class FoodController
{
    private FoodSettingsRepository $foodSettingsRepository;
    private RestaurantRepository $restaurantRepository;
    private SettingsRepository $settingsRepository;

    public function __construct()
    {
        $this->foodSettingsRepository = new FoodSettingsRepository();
        $this->restaurantRepository = new RestaurantRepository();
        $this->settingsRepository = new SettingsRepository();
    }

    public function index(): void
    {
        $appSettings = $this->settingsRepository->getAll();
        $foodSettings = $this->foodSettingsRepository->getAll();
        $restaurants = $this->restaurantRepository->getAll();

        $viewModel = new FoodViewModel($appSettings, $foodSettings, $restaurants);

        require __DIR__ . '/../Views/Food/Index.php';
    }

    public function restaurant(int $id): void
    {
        $restaurant = $this->restaurantRepository->getById($id);

        if (!$restaurant) {
            throw new NotFoundException('Restaurant not found');
        }

        $appSettings = $this->settingsRepository->getAll();
        $foodSettings = $this->foodSettingsRepository->getAll();


        require __DIR__ . '/../Views/Food/Restaurant.php';
    }
}