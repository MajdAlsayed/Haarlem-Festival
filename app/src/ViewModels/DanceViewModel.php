<?php

namespace App\ViewModels;

use App\Models\Event;

class DanceViewModel
{
    public function __construct(
        /** @var Event[] */
        public array $events,
        /** @var Event[] */
        public array $fridayEvents,
        /** @var Event[] */
        public array $saturdayEvents,
        /** @var Event[] */
        public array $sundayEvents,
        /** @var array<int, array{name: string, slug: string|null, bio: string|null, image: string}> */
        public array $artists = [],
        public string $pageTitle = 'Dance Festival'
    ) {
    }
}
