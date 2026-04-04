<?php

namespace App\ViewModels;

use App\Models\Restaurant;

class FoodViewModel
{
    public array $foodSettings;

    /** @var Restaurant[] */
    public array $restaurants;

    /**
     * @param array        $foodSettings
     * @param Restaurant[] $restaurants
     */
    public function __construct(array $foodSettings, array $restaurants)
    {
        $this->foodSettings = $foodSettings;
        $this->restaurants  = $restaurants;
    }
}