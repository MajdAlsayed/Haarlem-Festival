<?php
declare(strict_types=1);

namespace App\ViewModels;

final class JazzViewModel
{
    /**
     * @param array<int, array<string,mixed>> $events
     * @param array<string, string> $eventCardImages map event title => image filename in /images/jazz/
     * @param array<int, string> $allEventsOrder event titles in display order for All Events grid
     */
    public function __construct(
        public array $events,
        public string $pageTitle = 'Jazz Festival',
        public array $eventCardImages = [],
        public array $allEventsOrder = []
    ) {}
}