<?php

namespace App\ViewModels;

class StoriesViewModel
{
    /**
     * @var string Currently selected day filter (all, thursday, friday, saturday, sunday)
     */
    public string $selectedDay     = 'all';

    /**
     * @var array Array of story records with metadata
     */
    public array  $stories         = [];

    /**
     * @var array Featured/highlighted stories for display
     */
    public array  $featured        = [];

    /**
     * @var array Schedule grouped by language (NL, ENG) and day
     */
    public array  $schedule        = ['NL' => [], 'ENG' => []];

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
     * Constructor for StoriesViewModel
     * Uses isset() and empty() for explicit data validation.
     * @param array $data The data array containing story information
     * @param string $selectedDay The currently selected day filter
     */
    public function __construct(array $data, string $selectedDay)
    {
        // Validate and assign selectedDay
        $this->selectedDay     = !empty($selectedDay) ? $selectedDay : 'all';
        
        // Validate and assign stories data using isset
        $this->stories         = isset($data['stories']) && is_array($data['stories']) ? $data['stories'] : [];
        $this->featured        = isset($data['featured']) && is_array($data['featured']) ? $data['featured'] : [];
        $this->schedule        = isset($data['schedule']) && is_array($data['schedule']) ? $data['schedule'] : ['NL' => [], 'ENG' => []];
        $this->pageTitle       = isset($data['pageTitle']) && !empty($data['pageTitle']) ? $data['pageTitle'] : 'Stories';
        $this->story           = isset($data['story']) && is_array($data['story']) ? $data['story'] : null;
        $this->detailPage      = isset($data['detailPage']) && is_array($data['detailPage']) ? $data['detailPage'] : null;
        $this->settings        = isset($data['settings']) && is_array($data['settings']) ? $data['settings'] : [];
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
     * Get hero images for stories landing page
     * @return array Array of hero image paths
     */
    public function getStoriesHeroImages(): array
    {
        return [
            '/images/Stories/stories-home-image-main1.png',
            '/images/Stories/stories-home-image-main2.jpg',
            '/images/Stories/stories-home-image-main3.jpg',
        ];
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
        
        return (string)$this->story['start_time'];
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
     * Get target audience text for the story
     * 
     * Returns the target audience category for this story.
     * Uses isset() and empty() validation.
     *
     * @return string Audience or empty string
     */
    public function getStoryAudienceText(): string
    {
        if (!isset($this->story) || empty($this->story) || !isset($this->story['audience'])) {
            return '';
        }
        
        return (string)$this->story['audience'];
    }

    /**
     * Get story description text

     * @return string Story description or empty string
     */
    public function getStoryDescriptionText(): string
    {
        if (!isset($this->story) || empty($this->story) || !isset($this->story['description'])) 
        {
            return '';
        }
        
        return (string)$this->story['description'];
    }

    /**
     * Get hero image path for the story
     * @return string Story hero image path
     */
    public function getStoryHeroImage(): string
    {
        if (!isset($this->story) || empty($this->story) || !isset($this->story['image_path'])) {
            return '/images/Stories/cards/default.jpg';
        }
        
        return (string)$this->story['image_path'];
    }

    /**
     * Get assets for "Omdenken" template story
     * @return array Associative array of Omdenken template images
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
     * Get assets for "Buurderij" template story
     * @return array Associative array of Buurderij template images
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
}
