<?php

namespace App\ViewModels;

use App\Models\Event;

class DanceViewModel
{
    public function __construct(
        /** @var Event[] For featured section (e.g. first 3 of all dance) */
        public array $events,
        /** @var Event[] */
        public array $fridayEvents,
        /** @var Event[] */
        public array $saturdayEvents,
        /** @var Event[] */
        public array $sundayEvents,
        public string $pageTitle = 'Dance Festival'
    ) {
    }
}
