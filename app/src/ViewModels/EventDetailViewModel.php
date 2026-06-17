<?php

namespace App\ViewModels;

use App\Models\Event;

/** View model for a single Dance event (map, gallery, breadcrumbs, formatted labels). */
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

    /** @var list<array<string,mixed>> */
    public array $eventTickets = [];

    public ?string $cartFlashSuccess = null;

    public ?string $cartFlashError = null;

    /** Dance day pass for this event's weekday (pass_day), with optional `stock` from controller. */
    public ?array $danceDayPass = null;

    /** Dance all-access weekend pass, with optional `stock` from controller. */
    public ?array $danceAllAccessPass = null;

    // prepared, ready-to-render values (filled by DanceEventService)
    public string $dateTimeLine = '';
    public string $venueLine = '';
    public string $ticketsFigmaTitle = '';
    public string $pageHeroTitle = '';
    public string $cartReturn = '';
    public string $cartFormCsrf = '';
    public string $standardCellClass = 'event-detail-tickets-cell';
    public string $dayCellClass = 'event-detail-tickets-cell';
    /** @var list<array<string, mixed>> */
    public array $eventTicketCards = [];
    public ?array $dayPassCard = null;
    public ?array $festivalPassCard = null;

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
