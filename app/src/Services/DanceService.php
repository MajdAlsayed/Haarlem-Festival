<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\ArtistServiceInterface;
use App\Contracts\ServiceInterface\DanceServiceInterface;
use App\Contracts\ServiceInterface\DanceSettingsServiceInterface;
use App\Contracts\ServiceInterface\EventServiceInterface;
use App\Contracts\ServiceInterface\SettingsServiceInterface;
use App\Models\Event;
use App\ViewModels\DanceArtistCard;
use App\ViewModels\DanceDayPanel;
use App\ViewModels\DanceEventCard;
use App\ViewModels\DanceViewModel;

// dance page data for the view
class DanceService implements DanceServiceInterface
{
    private const CATEGORY_DANCE = 'dance';
    private const DAYS = ['friday', 'saturday', 'sunday'];

    private ?array $danceSettings = null;
    private ?array $appSettings = null;

    public function __construct(
        private EventServiceInterface $eventService,
        private DanceSettingsServiceInterface $danceSettingsService,
        private ArtistServiceInterface $artistService,
        private SettingsServiceInterface $settingsService,
    ) {
    }

    // controller calls this, viewmodel comes from page data
    public function buildIndexViewModel(): DanceViewModel
    {
        return DanceViewModel::fromPageData($this->getDancePageData());
    }

    // hero, about, schedule, artists — everything /dance needs
    public function getDancePageData(): array
    {
        $settings = $this->getDanceSettings();
        $grouped = $this->getEventsGroupedByDay();
        $dayLabels = $this->buildDayLabels($settings);

        return [
            'pageTitle' => $this->resolvePageTitle($settings),
            'appSettings' => $this->appSettings(),
            'breadcrumbs' => $this->buildBreadcrumbs($settings),
            'hero' => $this->loadHeroData($settings),
            'about' => $this->loadAboutData($settings),
            'featured' => $this->loadFeaturedData($grouped, $settings),
            'schedule' => $this->loadScheduleData($grouped, $settings, $dayLabels),
            'artists' => $this->loadArtistsData($settings),
        ];
    }

    // friday / saturday / sunday buckets for the schedule
    public function getEventsGroupedByDay(): array
    {
        $settings = $this->getDanceSettings();
        $grouped = [];

        foreach (self::DAYS as $day) {
            $events = $this->eventService->getByCategoryAndDay(self::CATEGORY_DANCE, $day);
            $venueOrder = $this->getVenueOrder($settings, 'venue_order_' . $day);
            $grouped[$day] = $this->sortEventsByVenueOrder($events, $venueOrder);
        }

        $grouped['all'] = $this->eventService->getByCategory(self::CATEGORY_DANCE);

        return $grouped;
    }

    // homepage artist cards in display order
    public function getArtistsOrdered(): array
    {
        $settings = $this->getDanceSettings();

        if (isset($settings['artists']) && is_array($settings['artists']) && $settings['artists'] !== []) {
            return $settings['artists'];
        }

        return $this->getDanceArtistsFromDatabase($settings);
    }

    // merged dance_settings + config defaults (cached per request)
    public function getDanceSettings(): array
    {
        if ($this->danceSettings === null) {
            $this->danceSettings = $this->danceSettingsService->getMergedWithConfig();
        }

        return $this->danceSettings;
    }

    private function loadHeroData(array $settings): array
    {
        return [
            'title' => $this->settingTextOr($settings, 'hero_title', 'Dance'),
            'image' => $this->danceImage($this->settingText($settings, 'hero_image')),
            'subtitle' => $this->settingText($settings, 'hero_subtitle'),
            'buttonText' => $this->settingTextOr($settings, 'hero_button_text', 'Explore events'),
            'buttonUrl' => $this->settingTextOr($settings, 'hero_button_url', '#featured-events'),
        ];
    }

    private function loadAboutData(array $settings): array
    {
        return [
            'heading' => $this->settingText($settings, 'about_section_heading'),
            'paragraphs' => $this->settingList($settings, 'about_paragraphs'),
        ];
    }

    private function loadFeaturedData(array $grouped, array $settings): array
    {
        $events = $this->buildFeaturedEvents($grouped['saturday'], $grouped['sunday']);

        return [
            'title' => $this->settingText($settings, 'featured_section_title'),
            'cards' => $this->buildFeaturedCards($events, $settings),
        ];
    }

    private function loadScheduleData(array $grouped, array $settings, array $dayLabels): array
    {
        return [
            'title' => $this->settingText($settings, 'all_events_section_title'),
            'dayLabels' => $dayLabels,
            'panels' => $this->buildDayPanels($grouped, $settings, $dayLabels),
        ];
    }

    private function loadArtistsData(array $settings): array
    {
        return [
            'title' => $this->settingText($settings, 'artists_section_title'),
            'infoLabel' => $this->settingText($settings, 'artist_info_label'),
            'showMoreLabel' => $this->settingText($settings, 'show_more_artists_label'),
            'cards' => $this->buildArtistCards($this->getArtistsOrdered()),
        ];
    }

    private function buildDayLabels(array $settings): array
    {
        $labels = [];
        foreach (self::DAYS as $day) {
            $labels[$day] = $this->settingText($settings, 'day_label_' . $day);
        }

        return $labels;
    }

    private function buildFeaturedEvents(array $saturdayEvents, array $sundayEvents): array
    {
        return array_merge(
            array_slice($saturdayEvents, 0, 1),
            array_slice($sundayEvents, 1, 2)
        );
    }

    private function buildFeaturedCards(array $events, array $settings): array
    {
        $images = $this->settingList($settings, 'featured_images');
        $genres = $this->settingList($settings, 'featured_genre_labels');
        $defaultDay = $this->settingTextOr($settings, 'default_event_day', self::DAYS[0]);
        $defaultTime = $this->defaultEventTime();

        $cards = [];
        foreach ($events as $i => $event) {
            $day = $this->resolveEventDay($event, $defaultDay);
            $startTime = $this->resolveStartTime($event, $defaultTime);

            $cards[] = $this->toEventCard(
                $event,
                $this->pickListItem($images, $i, rotate: false, useFirstWhenMissing: true),
                $this->pickListItem($genres, $i, rotate: false),
                ucfirst($day) . ' • ' . $startTime,
            );
        }

        return $cards;
    }

    private function buildDayPanels(array $grouped, array $settings, array $dayLabels): array
    {
        $defaultTime = $this->defaultEventTime();
        $panels = [];

        foreach (self::DAYS as $day) {
            $images = $this->settingList($settings, $day . '_images');
            $genres = $this->settingList($settings, $day . '_genres');
            $label = $dayLabels[$day];

            $cards = [];
            foreach ($grouped[$day] as $i => $event) {
                $startTime = $this->resolveStartTime($event, $defaultTime);
                $cards[] = $this->toEventCard(
                    $event,
                    $this->pickListItem($images, $i, rotate: true),
                    $this->pickListItem($genres, $i, rotate: true),
                    $label . ' • ' . $startTime,
                );
            }

            $panels[$day] = new DanceDayPanel(
                panelClass: $day === self::DAYS[0] ? 'dance-events-panel active' : 'dance-events-panel',
                cards: $cards,
            );
        }

        return $panels;
    }

    private function buildArtistCards(array $artists): array
    {
        $cards = [];
        foreach ($artists as $artist) {
            if (!is_array($artist)) {
                continue;
            }

            $slug = $this->arrayText($artist, 'slug');
            $url = $slug !== '' ? '/dance/artist/' . rawurlencode($slug) : '#';

            $cards[] = new DanceArtistCard(
                name: $this->arrayText($artist, 'name'),
                bio: $this->arrayText($artist, 'bio'),
                imagePath: $this->danceImage($this->arrayText($artist, 'image')),
                url: $url,
            );
        }

        return $cards;
    }

    private function toEventCard(Event $event, string $imageName, string $genre, string $whenLabel): DanceEventCard
    {
        return new DanceEventCard(
            id: (int) $event->id,
            title: (string) $event->title,
            description: (string) $event->description,
            imagePath: $this->danceImage($imageName),
            genre: $genre,
            venueLine: $event->venueName . ', ' . $event->venueCity,
            whenLabel: $whenLabel,
        );
    }

    private function pickListItem(array $items, int $index, bool $rotate, bool $useFirstWhenMissing = false): string
    {
        if ($items === []) {
            return '';
        }

        if ($rotate) {
            return (string) $items[$index % count($items)];
        }

        if (isset($items[$index])) {
            return (string) $items[$index];
        }

        if ($useFirstWhenMissing && isset($items[0])) {
            return (string) $items[0];
        }

        return '';
    }

    private function resolveEventDay(Event $event, string $defaultDay): string
    {
        if ($event->eventDay === null || $event->eventDay === '') {
            return $defaultDay;
        }

        return (string) $event->eventDay;
    }

    private function resolveStartTime(Event $event, string $defaultTime): string
    {
        if ($event->startTime === null || $event->startTime === '') {
            return $defaultTime;
        }

        return (string) $event->startTime;
    }

    private function buildBreadcrumbs(array $settings): array
    {
        return [
            ['label' => $this->settingText($settings, 'breadcrumb_home_label'), 'url' => '/'],
            ['label' => $this->settingText($settings, 'breadcrumb_dance_label'), 'url' => null],
        ];
    }

    private function resolvePageTitle(array $settings): string
    {
        return $this->settingText($settings, 'dance_page_title');
    }

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

    private function venuePosition(?int $venueId, array $venueOrder): int
    {
        $position = array_search($venueId, $venueOrder, true);

        return $position === false ? count($venueOrder) : $position;
    }

    private function getVenueOrder(array $settings, string $key): array
    {
        if (isset($settings[$key]) && is_array($settings[$key])) {
            return array_map('intval', $settings[$key]);
        }

        return [];
    }

    private function getDanceArtistsFromDatabase(array $settings): array
    {
        $slugs = $this->settingList($settings, 'dance_index_artist_slugs');
        $allArtists = $this->artistService->getAllOrdered();
        $artists = [];

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

    private function appSettings(): array
    {
        if ($this->appSettings === null) {
            $this->appSettings = $this->settingsService->getAll();
        }

        return $this->appSettings;
    }

    private function defaultEventTime(): string
    {
        $app = $this->appSettings();

        return isset($app['default_event_time']) ? (string) $app['default_event_time'] : '';
    }

    private function settingTextOr(array $settings, string $key, string $fallback): string
    {
        $value = $this->settingText($settings, $key);

        return $value !== '' ? $value : $fallback;
    }

    private function settingList(array $settings, string $key): array
    {
        if (isset($settings[$key]) && is_array($settings[$key])) {
            return $settings[$key];
        }

        return [];
    }

    private function settingText(array $settings, string $key): string
    {
        if (isset($settings[$key]) && is_string($settings[$key])) {
            return $settings[$key];
        }

        return '';
    }

    private function danceImage(string $name): string
    {
        if ($name === '') {
            return '';
        }

        return '/images/dance/' . rawurlencode($name);
    }

    private function arrayText(array $row, string $key): string
    {
        return isset($row[$key]) ? (string) $row[$key] : '';
    }

}