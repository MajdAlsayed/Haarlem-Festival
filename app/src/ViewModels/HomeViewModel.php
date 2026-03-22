<?php

namespace App\ViewModels;

use App\Models\EventType;
use App\Models\Page;

class HomeViewModel
{
    /**
     * @param array<string, string> $cmsHome Hero / welcome / about copy (merged config + site_settings).
     */
    public function __construct(
        public Page $page,
        /** @var EventType[] Homepage category cards (one per event type) */
        public array $categories,
        public array $cmsHome = []
    ) {
    }
}
