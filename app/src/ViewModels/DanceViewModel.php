<?php

namespace App\ViewModels;

use App\Models\Event;

/** View model for the Dance index: keeps the template thin by passing structured data only. */
class DanceViewModel
{
    public array $events;
    public array $fridayEvents;
    public array $saturdayEvents;
    public array $sundayEvents;
    public array $featuredEvents;
    public array $artists;
    public array $appSettings;
    public array $danceSettings;
    public array $breadcrumbs;
    public string $pageTitle;

    public function __construct(
        array $events,
        array $fridayEvents,
        array $saturdayEvents,
        array $sundayEvents,
        array $featuredEvents,
        array $artists,
        array $appSettings,
        array $danceSettings,
        array $breadcrumbs,
        string $pageTitle = 'Dance Festival'
    ) {
        $this->events = $events;
        $this->fridayEvents = $fridayEvents;
        $this->saturdayEvents = $saturdayEvents;
        $this->sundayEvents = $sundayEvents;
        $this->featuredEvents = $featuredEvents;
        $this->artists = $artists;
        $this->appSettings = $appSettings;
        $this->danceSettings = $danceSettings;
        $this->breadcrumbs = $breadcrumbs;
        $this->pageTitle = $pageTitle;
    }
}
