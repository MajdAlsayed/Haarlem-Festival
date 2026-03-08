<?php

namespace App\ViewModels;

use App\Models\EventType;
use App\Models\Page;

class HomeViewModel
{
    public function __construct(
        public Page $page,
        /** @var EventType[] Homepage category cards (one per event type) */
        public array $categories
    ) {
    }
}
