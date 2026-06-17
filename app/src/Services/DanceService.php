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

    // gather everything for the dance page and hand back one ready-to-render view model
    public function buildIndexViewModel(): DanceViewModel
    {
        $grouped = $this->getEventsGroupedByDay();
        $settings = $this->getDanceSettings();
        $featuredEvents = $this->buildFeaturedEvents($grouped['saturday'], $grouped['sunday']);

        $dayLabels = [
            'friday' => $this->settingText($settings, 'day_label_friday'),
            'saturday' => $this->settingText($settings, 'day_label_saturday'),
            'sunday' => $this->settingText($settings, 'day_label_sunday'),
        ];

        return new DanceViewModel(
            pageTitle: $this->resolvePageTitle($settings),
            appSettings: $this->settingsRepository->getAll(),
            breadcrumbs: $this->buildBreadcrumbs($settings),
            heroTitle: $this->settingTextOr($settings, 'hero_title', 'Dance'),
            heroImage: $this->danceImage($this->settingText($settings, 'hero_image')),
            heroSubtitle: $this->settingText($settings, 'hero_subtitle'),
            heroButtonText: $this->settingTextOr($settings, 'hero_button_text', 'Explore events'),
            heroButtonUrl: $this->settingTextOr($settings, 'hero_button_url', '#featured-events'),
            aboutHeading: $this->settingText($settings, 'about_section_heading'),
            aboutParagraphs: $this->settingList($settings, 'about_paragraphs'),
            featuredTitle: $this->settingText($settings, 'featured_section_title'),
            featuredCards: $this->buildFeaturedCards($featuredEvents, $settings),
            allEventsTitle: $this->settingText($settings, 'all_events_section_title'),
            dayLabels: $dayLabels,
            dayPanels: $this->buildDayPanels($grouped, $settings, $dayLabels),
            artistsTitle: $this->settingText($settings, 'artists_section_title'),
            artistInfoLabel: $this->settingText($settings, 'artist_info_label'),
            showMoreLabel: $this->settingText($settings, 'show_more_artists_label'),
            artistCards: $this->buildArtistCards($this->getArtistsOrdered()),
        );
    }

    // featured cards: each event with its image, genre and time already worked out
    /** @return list<array<string, mixed>> */
    private function buildFeaturedCards(array $events, array $settings): array
    {
        $images = $this->settingList($settings, 'featured_images');
        $genres = $this->settingList($settings, 'featured_genre_labels');

        $cards = [];
        foreach ($events as $i => $event) {
            // reuse the first image when the cms has fewer entries than events
            $imageName = '';
            if (isset($images[$i])) {
                $imageName = (string) $images[$i];
            } elseif (isset($images[0])) {
                $imageName = (string) $images[0];
            }

            $genre = '';
            if (isset($genres[$i])) {
                $genre = (string) $genres[$i];
            }

            $day = $event->eventDay;
            if ($day === null || $day === '') {
                $day = 'friday';
            }
            $startTime = $event->startTime;
            if ($startTime === null || $startTime === '') {
                $startTime = '20:00';
            }

            $cards[] = [
                'id' => (int) $event->id,
                'title' => (string) $event->title,
                'description' => (string) $event->description,
                'imagePath' => $this->danceImage($imageName),
                'genre' => $genre,
                'timeLine' => ucfirst($day) . ' • ' . $startTime,
                'venue' => $event->venueName . ', ' . $event->venueCity,
            ];
        }

        return $cards;
    }

    // one panel per day, each with its ready event cards (images/genres rotate when there are more events than entries)
    /** @return array<string, array<string, mixed>> */
    private function buildDayPanels(array $grouped, array $settings, array $dayLabels): array
    {
        $appSettings = $this->settingsRepository->getAll();
        $defaultTime = '';
        if (isset($appSettings['default_event_time'])) {
            $defaultTime = (string) $appSettings['default_event_time'];
        }

        $panels = [];
        foreach (self::DAYS as $day) {
            $images = $this->settingList($settings, $day . '_images');
            $genres = $this->settingList($settings, $day . '_genres');
            $label = $dayLabels[$day];

            $cards = [];
            foreach ($grouped[$day] as $i => $event) {
                $imageName = '';
                if ($images !== []) {
                    $imageName = (string) $images[$i % count($images)];
                }
                $genre = '';
                if ($genres !== []) {
                    $genre = (string) $genres[$i % count($genres)];
                }
                $startTime = $event->startTime;
                if ($startTime === null || $startTime === '') {
                    $startTime = $defaultTime;
                }

                $cards[] = [
                    'id' => (int) $event->id,
                    'title' => (string) $event->title,
                    'description' => (string) $event->description,
                    'venueName' => (string) $event->venueName,
                    'venueCity' => (string) $event->venueCity,
                    'imagePath' => $this->danceImage($imageName),
                    'genre' => $genre,
                    'dateTime' => $label . ' • ' . $startTime,
                ];
            }

            $panelClass = 'dance-events-panel';
            if ($day === 'friday') {
                $panelClass = 'dance-events-panel active';
            }

            $panels[$day] = [
                'panelClass' => $panelClass,
                'cards' => $cards,
            ];
        }

        return $panels;
    }

    // artist cards for the homepage strip, with image path and profile url resolved
    /** @return list<array<string, string>> */
    private function buildArtistCards(array $artists): array
    {
        $cards = [];
        foreach ($artists as $artist) {
            if (!is_array($artist)) {
                continue;
            }

            $slug = $this->arrayText($artist, 'slug');
            $url = '#';
            if ($slug !== '') {
                $url = '/dance/artist/' . rawurlencode($slug);
            }

            $cards[] = [
                'name' => $this->arrayText($artist, 'name'),
                'bio' => $this->arrayText($artist, 'bio'),
                'imagePath' => $this->danceImage($this->arrayText($artist, 'image')),
                'url' => $url,
            ];
        }

        return $cards;
    }

    // a text setting, or the given fallback when it's empty
    private function settingTextOr(array $settings, string $key, string $fallback): string
    {
        $value = $this->settingText($settings, $key);
        if ($value !== '') {
            return $value;
        }

        return $fallback;
    }

    // a list setting, or an empty list when it's missing
    /** @return array<int, mixed> */
    private function settingList(array $settings, string $key): array
    {
        if (isset($settings[$key]) && is_array($settings[$key])) {
            return $settings[$key];
        }

        return [];
    }

    // build the public image path for a dance image, or empty when there's no name
    private function danceImage(string $name): string
    {
        if ($name === '') {
            return '';
        }

        return '/images/dance/' . rawurlencode($name);
    }

    // read a text field from an artist row as a clean string
    private function arrayText(array $row, string $key): string
    {
        if (isset($row[$key])) {
            return (string) $row[$key];
        }

        return '';
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
