<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\DanceServiceInterface;
use App\Models\Event;
use App\Repositories\ArtistsRepository;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\EventRepository;
use App\Repositories\SettingsRepository;
use App\ViewModels\DanceViewModel;

// builds all the data the public /dance page needs
class DanceService implements DanceServiceInterface
{
    private const CATEGORY_DANCE = 'dance';
    private const DAYS = ['friday', 'saturday', 'sunday'];

    private EventRepository $eventRepository;
    private DanceSettingsRepository $danceSettingsRepository;
    private ArtistsRepository $artistsRepository;
    private SettingsRepository $settingsRepository;

    // load the settings once and keep them here so we don't fetch them every time
    private ?array $danceSettings = null;

    public function __construct(
        EventRepository $eventRepository,
        DanceSettingsRepository $danceSettingsRepository,
        ArtistsRepository $artistsRepository,
        SettingsRepository $settingsRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->danceSettingsRepository = $danceSettingsRepository;
        $this->artistsRepository = $artistsRepository;
        $this->settingsRepository = $settingsRepository;
    }

    // gather everything for the dance page and hand back one view model
    public function buildIndexViewModel(): DanceViewModel
    {
        $grouped = $this->getEventsGroupedByDay();
        $danceSettings = $this->getDanceSettings();

        return new DanceViewModel(
            $grouped['all'],
            $grouped['friday'],
            $grouped['saturday'],
            $grouped['sunday'],
            $this->buildFeaturedEvents($grouped['saturday'], $grouped['sunday']),
            $this->getArtistsOrdered(),
            $this->settingsRepository->getAll(),
            $danceSettings,
            $this->buildBreadcrumbs($danceSettings),
            $this->resolvePageTitle($danceSettings)
        );
    }

    // featured strip = 1 saturday event + 2 sunday events
    /** @return Event[] */
    private function buildFeaturedEvents(array $saturdayEvents, array $sundayEvents): array
    {
        return array_merge(
            array_slice($saturdayEvents, 0, 1),
            array_slice($sundayEvents, 1, 2)
        );
    }

    private function buildBreadcrumbs(array $danceSettings): array
    {
        return [
            ['label' => $this->settingText($danceSettings, 'breadcrumb_home_label'), 'url' => '/'],
            ['label' => $this->settingText($danceSettings, 'breadcrumb_dance_label'), 'url' => null],
        ];
    }

    private function resolvePageTitle(array $danceSettings): string
    {
        return $this->settingText($danceSettings, 'dance_page_title');
    }

    // grab a text setting, or empty string if it's not there
    private function settingText(array $settings, string $key): string
    {
        if (isset($settings[$key]) && is_string($settings[$key])) {
            return $settings[$key];
        }

        return '';
    }

    // split the dance events per day and order each day by venue
    /** @return array<string, Event[]> */
    public function getEventsGroupedByDay(): array
    {
        $settings = $this->getDanceSettings();
        $grouped = [];

        foreach (self::DAYS as $day) {
            $events = $this->eventRepository->getByCategoryAndDay(self::CATEGORY_DANCE, $day);
            $venueOrder = $this->getVenueOrder($settings, 'venue_order_' . $day);
            $grouped[$day] = $this->sortEventsByVenueOrder($events, $venueOrder);
        }

        $grouped['all'] = $this->eventRepository->getByCategory(self::CATEGORY_DANCE);

        return $grouped;
    }

    // sort by venue order first, then by start time when two events are at the same venue
    /** @return Event[] */
    private function sortEventsByVenueOrder(array $events, array $venueOrder): array
    {
        usort($events, function (Event $first, Event $second) use ($venueOrder) {
            $firstVenue = $this->venuePosition($first->venueId, $venueOrder);
            $secondVenue = $this->venuePosition($second->venueId, $venueOrder);

            if ($firstVenue !== $secondVenue) {
                return $firstVenue - $secondVenue;
            }

            return strcmp((string) $first->startTime, (string) $second->startTime);
        });

        return $events;
    }

    // where a venue sits in the configured order; venues that aren't listed go to the end
    private function venuePosition(?int $venueId, array $venueOrder): int
    {
        $position = array_search($venueId, $venueOrder, true);

        if ($position === false) {
            return count($venueOrder);
        }

        return $position;
    }

    // read the venue id list for a day, or empty list if it's missing
    /** @return int[] */
    private function getVenueOrder(array $settings, string $key): array
    {
        if (isset($settings[$key]) && is_array($settings[$key])) {
            return array_map('intval', $settings[$key]);
        }

        return [];
    }

    // homepage artists: use the cms list if it has entries, otherwise pull them from the db by slug
    public function getArtistsOrdered(): array
    {
        $settings = $this->getDanceSettings();

        if (isset($settings['artists']) && is_array($settings['artists']) && count($settings['artists']) > 0) {
            return $settings['artists'];
        }

        return $this->getDanceArtistsFromDatabase($settings);
    }

    private function getDanceArtistsFromDatabase(array $settings): array
    {
        $slugs = [];
        if (isset($settings['dance_index_artist_slugs']) && is_array($settings['dance_index_artist_slugs'])) {
            $slugs = $settings['dance_index_artist_slugs'];
        }

        $allArtists = $this->artistsRepository->getAllOrdered();
        $artists = [];

        // keep only the artists i want on the homepage, in the order the slugs are listed
        foreach ($slugs as $slug) {
            foreach ($allArtists as $artist) {
                if ($artist['slug'] === $slug) {
                    $artists[] = $artist;
                    break;
                }
            }
        }

        return $artists;
    }

    // load the merged config + database settings once and reuse them
    public function getDanceSettings(): array
    {
        if ($this->danceSettings === null) {
            $this->danceSettings = $this->danceSettingsRepository->getMergedWithConfig();
        }

        return $this->danceSettings;
    }
}
