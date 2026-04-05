<?php

namespace App\ViewModels;

use App\Models\Event;

/**
 * Data for the Dance event detail view. The controller fills this; the view only uses these properties.
 */
class EventDetailViewModel
{
    public Event $event;
    public string $pageTitle;
    public string $heroImage;
    public array $galleryImages;
    public array $breadcrumbs;
    public array $appSettings;
    public string $formattedDate;
    public string $startTime;
    public string $locationDisplay;
    public float $mapLat;
    public float $mapLon;
    public string $mapQuery;
    public string $artistsDisplay;
    public string $eventSubtitle;

    public function __construct(
        Event $event,
        string $heroImage,
        array $galleryImages,
        array $breadcrumbs,
        array $appSettings,
        string $formattedDate,
        string $startTime,
        string $locationDisplay,
        float $mapLat,
        float $mapLon,
        string $mapQuery,
        string $artistsDisplay,
        string $eventSubtitle,
        string $pageTitle = ''
    ) {
        $this->event = $event;
        $this->heroImage = $heroImage;
        $this->galleryImages = $galleryImages;
        $this->breadcrumbs = $breadcrumbs;
        $this->appSettings = $appSettings;
        $this->formattedDate = $formattedDate;
        $this->startTime = $startTime;
        $this->locationDisplay = $locationDisplay;
        $this->mapLat = $mapLat;
        $this->mapLon = $mapLon;
        $this->mapQuery = $mapQuery;
        $this->artistsDisplay = $artistsDisplay;
        $this->eventSubtitle = $eventSubtitle;
        $this->pageTitle = ($pageTitle !== '') ? $pageTitle : ($event->venueName . ' — ' . $event->title);
    }
}
