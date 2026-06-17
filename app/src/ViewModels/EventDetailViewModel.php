<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Event;

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

    public array $eventTickets = [];

    public ?string $cartFlashSuccess = null;

    public ?string $cartFlashError = null;

    public ?array $danceDayPass = null;

    public ?array $danceAllAccessPass = null;

    public string $dateTimeLine = '';
    public string $venueLine = '';
    public string $ticketsFigmaTitle = '';
    public string $pageHeroTitle = '';
    public string $cartReturn = '';
    public string $cartFormCsrf = '';
    public string $standardCellClass = 'event-detail-tickets-cell';
    public string $dayCellClass = 'event-detail-tickets-cell';

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

    public static function fromPageData(array $page): self
    {
        $hero = $page['hero'];
        $eventInfo = $page['eventInfo'];
        $display = $page['display'];
        $map = $page['map'];
        $tickets = $page['tickets'];
        $cart = $tickets['cart'];
        $cards = $tickets['cards'];

        $vm = new self(
            event: $page['event'],
            heroImage: (string) $hero['image'],
            galleryImages: $hero['galleryImages'],
            breadcrumbs: $page['breadcrumbs'],
            appSettings: $page['appSettings'],
            formattedDate: (string) $eventInfo['formattedDate'],
            startTime: (string) $eventInfo['startTime'],
            locationDisplay: (string) $eventInfo['locationDisplay'],
            mapLat: (float) $map['lat'],
            mapLon: (float) $map['lon'],
            mapQuery: (string) $map['query'],
            artistsDisplay: (string) $eventInfo['artistsDisplay'],
            eventSubtitle: (string) $eventInfo['eventSubtitle'],
        );

        $vm->eventTickets = $tickets['eventTickets'];
        $vm->danceDayPass = $tickets['danceDayPass'];
        $vm->danceAllAccessPass = $tickets['danceAllAccessPass'];
        $vm->dateTimeLine = (string) $display['dateTimeLine'];
        $vm->venueLine = (string) $display['venueLine'];
        $vm->pageHeroTitle = (string) $display['pageHeroTitle'];
        $vm->ticketsFigmaTitle = (string) $display['ticketsFigmaTitle'];
        $vm->cartReturn = (string) $cart['return'];
        $vm->cartFormCsrf = (string) $cart['csrf'];
        $vm->standardCellClass = (string) $cards['standardCellClass'];
        $vm->dayCellClass = (string) $cards['dayCellClass'];
        $vm->eventTicketCards = $cards['event'];
        $vm->dayPassCard = $cards['dayPass'];
        $vm->festivalPassCard = $cards['festival'];

        return $vm;
    }
}
