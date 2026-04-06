<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\ArtistsRepository;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\EventRepository;

/**
 * Data for /dance: per-day venue order from dance_settings or defaults in dance.php; artist list from the same merged config.
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
        // Venues not listed in CMS/config go to the end, then we sort by start time inside the same slot.
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
     * Homepage artist strip: non-empty CMS `artists` JSON, else defaults from dance.php, else `artists` table rows
     * whose slug is listed in `dance_index_artist_slugs` (Hardwell / Tiësto — not Jazz slugs).
     */
    public function getArtistsOrdered(): array
    {
        $settings = $this->danceSettingsRepository->getMergedWithConfig();
        $artists = $settings['artists'] ?? null;
        if (is_array($artists) && count($artists) > 0) {
            return $artists;
        }
        $config = require __DIR__ . '/../Config/dance.php';
        $fromConfig = $config['artists'] ?? [];
        if (is_array($fromConfig) && count($fromConfig) > 0) {
            return $fromConfig;
        }

        return $this->getDanceArtistsFromDatabase();
    }

    /**
     * @return list<array{name: string, slug: string, bio: string, image: string}>
     */
    private function getDanceArtistsFromDatabase(): array
    {
        $defaults = require __DIR__ . '/../Config/dance.php';
        $slugs = $defaults['dance_index_artist_slugs'] ?? ['hardwell', 'tiesto'];
        if (!is_array($slugs)) {
            $slugs = ['hardwell', 'tiesto'];
        }

        $repo = new ArtistsRepository();
        $all = $repo->getAllOrdered();
        $bySlug = [];
        foreach ($all as $row) {
            $s = $row['slug'] ?? null;
            if (is_string($s) && $s !== '') {
                $bySlug[$s] = $row;
            }
        }

        $out = [];
        foreach ($slugs as $slug) {
            if (!isset($bySlug[$slug])) {
                continue;
            }
            $r = $bySlug[$slug];
            $out[] = [
                'name' => (string) ($r['name'] ?? ''),
                'slug' => $slug,
                'bio' => (string) ($r['bio'] ?? ''),
                'image' => (string) ($r['image'] ?? ''),
            ];
        }

        return $out;
    }

    public function getDanceSettings(): array
    {
        return $this->danceSettingsRepository->getMergedWithConfig();
    }
}
