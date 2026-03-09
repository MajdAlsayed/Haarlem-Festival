<?php

namespace App\ViewModels;

use App\Models\Restaurant;

class FoodViewModel
{
    public array $appSettings;
    public array $foodSettings;

    /** @var Restaurant[] */
    public array $restaurants;

    /**
     * @param array $appSettings
     * @param array $foodSettings
     * @param Restaurant[] $restaurants
     */
    public function __construct(array $appSettings, array $foodSettings, array $restaurants)
    {
        $this->appSettings = $appSettings;
        $this->foodSettings = $foodSettings;
        $this->restaurants = $restaurants;
    }
}