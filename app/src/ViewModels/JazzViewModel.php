<?php
declare(strict_types=1);

namespace App\ViewModels;

/**
 * Small bag of data for the jazz homepage template — events plus which image file to show on each card
 * and in what order “All events” should list them (both come from the merged jazz config).
 */
final class JazzViewModel
{
    /**
     * Property bag only — no methods; the controller fills this and the view reads it.
     *
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