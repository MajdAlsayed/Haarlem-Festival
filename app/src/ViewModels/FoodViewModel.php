<?php

namespace App\ViewModels;

use App\Models\Event;

class FoodViewModel
{
    public function __construct(
        /** @var Event[] */
        public array $events,
        public array $foodSettings = [],
        public string $pageTitle = 'Food & Drinks'
    ) {
    }
}