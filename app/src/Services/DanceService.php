<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\EventRepository;

/**
 * Dance page: get events by day (sorted by venue order from database) and artists.
 * Artists for the Dance index come from dance config only (not the shared artists table).
 */
class DanceService
{
    private EventRepository $eventRepository;
    private DanceSettingsRepository $danceSettingsRepository;

    public function __construct(
        EventRepository $eventRepository,
        DanceSettingsRepository $danceSettingsRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->danceSettingsRepository = $danceSettingsRepository;
    }

    /**
     * Get dance events per day. Each day is sorted by venue order from dance_settings.
     */
    public function getEventsGroupedByDay(): array
    {
        $settings = $this->danceSettingsRepository->getMergedWithConfig();
        $venueOrderFriday = $this->getVenueOrder($settings, 'venue_order_friday');
        $venueOrderSaturday = $this->getVenueOrder($settings, 'venue_order_saturday');
        $venueOrderSunday = $this->getVenueOrder($settings, 'venue_order_sunday');

        $fridayEvents = $this->eventRepository->getByCategoryAndDay('dance', 'friday');
        $saturdayEvents = $this->eventRepository->getByCategoryAndDay('dance', 'saturday');
        $sundayEvents = $this->eventRepository->getByCategoryAndDay('dance', 'sunday');

        $fridayEvents = $this->sortEventsByVenueOrder($fridayEvents, $venueOrderFriday);
        $saturdayEvents = $this->sortEventsByVenueOrder($saturdayEvents, $venueOrderSaturday);
        $sundayEvents = $this->sortEventsByVenueOrder($sundayEvents, $venueOrderSunday);

        $all = $this->eventRepository->getByCategory('dance');

        return [
            'friday' => $fridayEvents,
            'saturday' => $saturdayEvents,
            'sunday' => $sundayEvents,
            'all' => $all,
        ];
    }

    private function sortEventsByVenueOrder(array $events, array $venueOrder): array
    {
        $unknownPosition = count($venueOrder);
        usort($events, function (Event $a, Event $b) use ($venueOrder, $unknownPosition) {
            $posA = array_search($a->venueId, $venueOrder, true);
            $posB = array_search($b->venueId, $venueOrder, true);
            if ($posA === false) {
                $posA = $unknownPosition;
            }
            if ($posB === false) {
                $posB = $unknownPosition;
            }
            if ($posA !== $posB) {
                return $posA <=> $posB;
            }
            return strcmp($a->startTime ?? '', $b->startTime ?? '');
        });
        return $events;
    }

    private function getVenueOrder(array $settings, string $key): array
    {
        $raw = $settings[$key] ?? null;
        if (is_array($raw)) {
            return array_map('intval', $raw);
        }
        return [];
    }

    /**
     * Dance index shows only artists defined in dance config (Hardwell, Tiësto).
     * Jazz artists (e.g. Gumbo Kings, Karsu, Gare du Nord) are not shown here.
     */
    public function getArtistsOrdered(): array
    {
        $settings = $this->danceSettingsRepository->getMergedWithConfig();
        $artists = $settings['artists'] ?? null;
        if (is_array($artists) && $artists !== []) {
            return $artists;
        }
        $config = require __DIR__ . '/../Config/dance.php';
        return $config['artists'] ?? [];
    }

    public function getDanceSettings(): array
    {
        return $this->danceSettingsRepository->getMergedWithConfig();
    }
}
