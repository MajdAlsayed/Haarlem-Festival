<?php

namespace App\ViewModels;

use App\Models\Event;
use App\Models\Page;

class HomeViewModel
{
    public function __construct(
        public Page $page,
        /** @var Event[] */
        public array $events
    ) {
    }
}
