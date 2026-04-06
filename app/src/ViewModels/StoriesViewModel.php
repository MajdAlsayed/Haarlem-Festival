<?php

namespace App\ViewModels;

class StoriesViewModel
{
    public string $selectedDay     = 'all';
    public array  $stories         = [];
    public array  $allVenueStories = [];
    public array  $schedule        = ['NL' => [], 'ENG' => []];
    public string $pageTitle       = 'Stories';
    public ?array $venue           = null;
    public ?array $story           = null;
    public ?array $detailPage      = null;

    public function __construct(array $data, string $selectedDay)
    {
        $this->selectedDay     = $selectedDay ?: 'all';
        $this->stories         = $data['stories']         ?? [];
        $this->allVenueStories = $data['allVenueStories'] ?? [];
        $this->schedule        = $data['schedule']        ?? ['NL' => [], 'ENG' => []];
        $this->pageTitle       = $data['pageTitle']       ?? 'Stories';
        $this->venue           = $data['venue']           ?? null;
        $this->story           = $data['story']           ?? null;
        $this->detailPage      = $data['detailPage']      ?? null;
    }


    public function getTemplate(): string
    {
        $template = strtolower(trim((string)($this->story['template'] ?? 'generic')));

        return in_array($template, ['omdenken', 'buurderij', 'generic'], true)
            ? $template
            : 'generic';
    }



    public function isActive(string $day): bool
    {
        return strtolower($this->selectedDay) === strtolower($day);
    }


    public function getStoriesHeroImages(): array
    {
        return [
            '/images/Stories/stories-home-image-main1.png',
            '/images/Stories/stories-home-image-main2.jpg',
            '/images/Stories/stories-home-image-main3.jpg',
        ];
    }


    public function getVenueHeroImage(): string
    {
        $venueId = (int)($this->venue['venue_id'] ?? 0);

        return match ($venueId) {
            2 => '/images/Stories/venues/de-schuur-heroimage.jpg',
            3 => '/images/Stories/venues/Kweekcafe-heroimage.jpg',
            default => '/images/Stories/venues/default-venue.jpg',
        };
    }


    public function getStoryVenueText(): string
    {
        $name = (string)($this->story['venue_name'] ?? '');
        $city = (string)($this->story['venue_city'] ?? '');
        return trim($name . ($city ? ', ' . $city : ''));
    }

    public function getStoryDayText(): string
    {
        return ucfirst((string)($this->story['event_day'] ?? ''));
    }

    public function getStoryTimeText(): string
    {
        return (string)($this->story['start_time'] ?? '');
    }

    public function getStoryAgeText(): string
    {
        return (string)($this->story['age'] ?? '');
    }

    public function getStoryLanguageText(): string
    {
        return (string)($this->story['language'] ?? '');
    }

    public function getStoryTypeText(): string
    {
        return (string)($this->story['story_type'] ?? '');
    }

    public function getStoryAudienceText(): string
    {
        return (string)($this->story['audience'] ?? '');
    }

    public function getStoryDescriptionText(): string
    {
        return (string)($this->story['description'] ?? '');
    }

    public function getStoryHeroImage(): string
    {
        return (string)($this->story['image_path'] ?? '/images/Stories/cards/default.jpg');
    }


    public function getOmdenkenAssets(): array
    {
        return [
            'hero'   => '/images/Stories/details/omdenken-hero.jpg',
            'block1' => '/images/Stories/details/omdenken2.jpg',
            'block2' => '/images/Stories/details/omdenken3.jpg',
            'poster' => '/images/Stories/details/omdenken1.jpg',
        ];
    }

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
}