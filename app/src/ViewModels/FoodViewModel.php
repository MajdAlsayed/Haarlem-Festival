<?php

namespace App\ViewModels;

use App\Models\Restaurant;

class FoodViewModel
{
    public array $events;
    public array $foodSettings;

    /** @var Restaurant[] */
    public array $restaurants;

    /**
     * @param array $events
     * @param array $foodSettings
     * @param Restaurant[] $restaurants
     */
    public function __construct(array $events, array $foodSettings, array $restaurants = [])
    {
        $this->events = $events;
        $this->foodSettings = $foodSettings;
        $this->restaurants = $restaurants;
    }
}