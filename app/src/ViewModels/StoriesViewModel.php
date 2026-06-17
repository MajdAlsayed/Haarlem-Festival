<?php

declare(strict_types=1);

namespace App\ViewModels;

class StoriesViewModel
{
    // Keeps story display formatting out of the controller and view.
    /**
     * @var string Currently selected day filter (all, thursday, friday, saturday, sunday)
     */
    public string $selectedDay     = 'all';

    /**
     * @var array Featured/highlighted stories for display
     */
    public array  $featured        = [];

    /**
     * @var string Page title for the current view
     */
    public string $pageTitle       = 'Stories';

    /**
     * @var array|null Single story detail data (null if not set)
     */
    public ?array $story           = null;

    /**
     * @var array|null Detail page metadata (null if not set)
     */
    public ?array $detailPage      = null;

    /**
     * @var array Settings from stories_settings table (key-value pairs)
     */
    public array  $settings        = [];

    /**
     * @var array Global app settings from site_settings/config
     */
    public array $appSettings = [];

    /**
     * Constructor for StoriesViewModel
     * Uses isset() and empty() for explicit data validation.
     * @param array $data The data array containing story information
     * @param string $selectedDay The currently selected day filter
     */
    public function __construct(array $data, string $selectedDay)
    {
        // Validate and assign selectedDay
        $this->selectedDay     = !empty($selectedDay) ? $selectedDay : 'all';
        
        $this->featured        = isset($data['featured']) && is_array($data['featured']) ? $data['featured'] : [];
        $this->pageTitle       = isset($data['pageTitle']) && !empty($data['pageTitle']) ? $data['pageTitle'] : 'Stories';
        $this->story           = isset($data['story']) && is_array($data['story']) ? $data['story'] : null;
        $this->detailPage      = isset($data['detailPage']) && is_array($data['detailPage']) ? $data['detailPage'] : null;
        $this->settings        = isset($data['settings']) && is_array($data['settings']) ? $data['settings'] : [];
        $this->appSettings     = isset($data['appSettings']) && is_array($data['appSettings']) ? $data['appSettings'] : [];
    }

    /**
     * @return array<int, string>
     */
    public function getHomeHeroImages(): array
    {
        return array_values(array_filter([
            $this->normalizeStoryImagePath($this->settings['hero_image_1'] ?? ''),
            $this->normalizeStoryImagePath($this->settings['hero_image_2'] ?? ''),
            $this->normalizeStoryImagePath($this->settings['hero_image_3'] ?? ''),
            $this->normalizeStoryImagePath($this->settings['hero_image_4'] ?? ''),
        ]));
    }

    /**
     * @return array<int, string>
     */
    public function getEventsHeroImages(): array
    {
        return array_values(array_filter([
            $this->normalizeStoryImagePath($this->settings['events_hero_image_1'] ?? ''),
            $this->normalizeStoryImagePath($this->settings['events_hero_image_2'] ?? ''),
        ]));
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getHomeExploreItems(): array
    {
        $decoded = json_decode((string)($this->settings['home_explore_items'] ?? '[]'), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<int, mixed>
     */
    public function getDetailHighlights(): array
    {
        return $this->decodeDetailJson('highlights');
    }

    /**
     * @return array<int, mixed>
     */
    public function getDetailGallery(): array
    {
        return $this->decodeDetailJson('gallery');
    }

    /**
     * @return array<int, array{name:string,lat:float,lng:float,label:string}>
     */
    public function getEventsMapLocations(): array
    {
        $decoded = json_decode((string)($this->settings['events_map_locations'] ?? '[]'), true);

        if (!is_array($decoded)) {
            return [];
        }

        $locations = [];
        foreach ($decoded as $location) {
            if (!is_array($location)) {
                continue;
            }

            $name = trim((string)($location['name'] ?? ''));
            $label = trim((string)($location['label'] ?? ''));

            if ($name === '' || !is_numeric($location['lat'] ?? null) || !is_numeric($location['lng'] ?? null)) {
                continue;
            }

            $locations[] = [
                'name' => $name,
                'lat' => (float)$location['lat'],
                'lng' => (float)$location['lng'],
                'label' => $label,
            ];
        }

        return $locations;
    }

    /**
     * Get the template type for story rendering
     * @return string The validated template type (defaults to 'generic')
     */
    public function getTemplate(): string
    {
        if (!isset($this->story) || empty($this->story) || !isset($this->story['template'])) {
            return 'generic';
        }
        
        $template = strtolower(trim((string)$this->story['template']));

        if ($template === '' || $template === 'generic') {
            $slug = strtolower(trim((string)($this->story['slug'] ?? '')));

            if ($slug === 'omdenken-podcast') {
                return 'omdenken';
            }

            if ($slug === 'the-story-of-buurderij-haarlem') {
                return 'buurderij';
            }
        }
        
        return in_array($template, ['omdenken', 'buurderij', 'generic'], true)
            ? $template
            : 'generic';
    }

    /**
     * Check if a specific day is currently active
     * @param string $day The day to check against current selection
     * @return bool True if the day is active, false otherwise
     */
    public function isActive(string $day): bool
    {
        return strtolower($this->selectedDay) === strtolower($day);
    }

    /**
     * Get capitalized event day text
     * 
     * Returns the event day with first letter capitalized.
     * Uses isset() and empty() validation.
     *
     * @return string Capitalized day text or empty string
     */
    public function getStoryDayText(): string
    {
        if (!isset($this->story) || empty($this->story) || !isset($this->story['event_day'])) {
            return '';
        }
        
        return ucfirst((string)$this->story['event_day']);
    }

    /**
     * Get story start time text
     * 
     * Returns the formatted start time for the story/event.
     * Uses isset() and empty() validation.
     *
     * @return string Start time or empty string
     */
    public function getStoryTimeText(): string
    {
        if (!isset($this->story) || empty($this->story) || !isset($this->story['start_time'])) {
            return '';
        }
        
        return $this->formatTime((string)$this->story['start_time']);
    }

    /**
     * Get story end time text
     *
     * @return string End time or empty string
     */
    public function getStoryEndTimeText(): string
    {
        if (!isset($this->story) || empty($this->story) || !isset($this->story['end_time'])) {
            return '';
        }

        return $this->formatTime((string)$this->story['end_time']);
    }

    /**
     * Get age requirement text for the story
     * 
     * Returns the age requirement/restriction for this story.
     * Uses isset() and empty() validation.
     *
     * @return string Age requirement or empty string
     */
    public function getStoryAgeText(): string
    {
        if (!isset($this->story) || empty($this->story) || !isset($this->story['age'])) {
            return '';
        }
        
        return (string)$this->story['age'];
    }

    /**
     * Get language text for the story
     * 
     * Returns the language code (NL, ENG, etc.) for this story.
     * Uses isset() and empty() validation.
     *
     * @return string Language code or empty string
     */
    public function getStoryLanguageText(): string
    {
        if (!isset($this->story) || empty($this->story) || !isset($this->story['language'])) {
            return '';
        }
        
        return (string)$this->story['language'];
    }

    /**
     * Get venue display text for the story
     *
     * @return string Venue name and city or empty string
     */
    public function getStoryVenueText(): string
    {
        if (!isset($this->story) || empty($this->story)) {
            return '';
        }

        $venue = trim((string)($this->story['venue_name'] ?? ''));
        $city = trim((string)($this->story['venue_city'] ?? ''));

        if ($venue === '') {
            return '';
        }

        return $city !== '' ? $venue . ', ' . $city : $venue;
    }

    /**
     * Get venue address from the linked database venue.
     *
     * @return string Venue address and city or empty string
     */
    public function getStoryVenueAddressText(): string
    {
        if (!isset($this->story) || empty($this->story)) {
            return '';
        }

        $address = trim((string)($this->story['venue_address'] ?? ''));
        $city = trim((string)($this->story['venue_city'] ?? ''));

        if ($address === '') {
            return $city;
        }

        return $city !== '' ? $address . ', ' . $city : $address;
    }

    /**
     * Google Maps embed URL based on database venue data.
     *
     * @return string Map embed URL or empty string
     */
    public function getStoryVenueMapUrl(): string
    {
        $query = $this->getStoryVenueAddressText();

        if ($query === '') {
            $query = $this->getStoryVenueText();
        }

        if ($query === '') {
            return '';
        }

        return 'https://www.google.com/maps?q=' . rawurlencode($query) . '&output=embed';
    }

    /**
     * Get story type/category text
     * 
     * Returns the type/category of this story.
     * Uses isset() and empty() validation.
     *
     * @return string Story type or empty string
     */
    public function getStoryTypeText(): string
    {
        if (!isset($this->story) || empty($this->story) || !isset($this->story['story_type'])) {
            return '';
        }
        
        return (string)$this->story['story_type'];
    }

    /**
     * Get ticket purchase URL with event context
     * 
     * Generates ticket page URL with event_id parameter if available.
     * Falls back to generic /tickets page if no event_id found.
     * Uses isset() and empty() validation.
     *
     * @return string Ticket page URL with optional event_id parameter
     */
    public function getTicketUrl(): string
    {
        if (!isset($this->story) || empty($this->story)) {
            return '/tickets';
        }
        
        $eventId = isset($this->story['event_id']) && !empty($this->story['event_id']) 
            ? (int)$this->story['event_id'] 
            : 0;
        
        if ($eventId > 0) {
            return '/tickets?event_id=' . $eventId;
        }
        
        return '/tickets';
    }

    public function getTicketDetailsId(): int
    {
        if (!isset($this->story) || empty($this->story)) {
            return 0;
        }

        return (int)($this->story['ticket_details_id'] ?? 0);
    }

    /**
     * Get assets for the Omdenken detail template.
     *
     * @return array<string, string>
     */
    public function getOmdenkenAssets(): array
    {
        return [
            'hero'   => '/images/Stories/details/omdenken-hero.jpg',
            'block1' => '/images/Stories/details/omdenken2.jpg',
            'block2' => '/images/Stories/details/omdenken3.jpg',
            'poster' => '/images/Stories/details/omdenken1.jpg',
        ];
    }

    /**
     * Get assets for the Buurderij detail template.
     *
     * @return array<string, string>
     */
    public function getBuurderijAssets(): array
    {
        return [
            'hero'     => '/images/Stories/details/Kweekcafehero.jpg',
            'main'     => '/images/Stories/details/Kweekcafe1.jpg',
            'main2'    => '/images/Stories/details/Kweekcafe2.jpg',
            'gallery1' => '/images/Stories/details/Kweekcafeg1.jpg',
            'gallery2' => '/images/Stories/details/Kweekcafeg2.jpg',
            'gallery3' => '/images/Stories/details/Kweekcafeg3.jpg',
        ];
    }

    private function formatTime(string $time): string
    {
        $time = trim($time);

        if (preg_match('/^\d{2}:\d{2}/', $time, $matches)) {
            return $matches[0];
        }

        return $time;
    }

    private function normalizeStoryImagePath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        return str_starts_with($path, '/') ? $path : '/images/Stories/' . $path;
    }

    /**
     * @return array<int, mixed>
     */
    private function decodeDetailJson(string $key): array
    {
        if (!is_array($this->detailPage)) {
            return [];
        }

        $decoded = json_decode((string)($this->detailPage[$key] ?? '[]'), true);

        return is_array($decoded) ? $decoded : [];
    }
}
