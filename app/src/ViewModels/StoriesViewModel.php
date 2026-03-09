<?php

namespace App\ViewModels;

class StoriesViewModel
{
    public string $selectedDay = 'all';

    // Home + venue list
    public array $stories = [];

    // For Explore More on venue page
    public array $allVenueStories = [];

    public array $schedule = ['NL' => [], 'ENG' => []];

    public string $pageTitle = 'Stories';
    public ?array $venue = null;
    public ?array $story = null;

    public function __construct(array $data, string $selectedDay)
    {
        $this->selectedDay = $selectedDay ?: 'all';

        $this->stories = $data['stories'] ?? [];
        $this->allVenueStories = $data['allVenueStories'] ?? [];

        $this->schedule = $data['schedule'] ?? ['NL' => [], 'ENG' => []];

        $this->pageTitle = $data['pageTitle'] ?? 'Stories';
        $this->venue = $data['venue'] ?? null;
        $this->story = $data['story'] ?? null;
    }

    public function isActive(string $day): bool
    {
        return strtolower($this->selectedDay) === strtolower($day);
    }
}