<?php

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\CartRepository;
use App\Repositories\PhotosRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketDetailsRepository;
use App\Repositories\TicketRepository;
use App\Repositories\TicketsRepository;
use App\Services\EventService;
use App\Services\TicketAvailabilityService;
use App\ViewModels\EventDetailViewModel;
use App\Exceptions\NotFoundException;

/**
 * Dance event detail (/dance/event/{id}): map coordinates from app config, images from CMS, title parsed for display lines.
 */
class EventDetailController
{
    private EventService $eventService;
    private PhotosRepository $photosRepository;
    private SettingsRepository $settingsRepository;

    public function __construct()
    {
        $this->eventService = new EventService(new \App\Repositories\EventRepository());
        $this->photosRepository = new PhotosRepository();
        $this->settingsRepository = new SettingsRepository();
    }

    public function show(int $eventId): void
    {
        try {
            $event = $this->eventService->getById($eventId);
        } catch (\Throwable $e) {
            throw new NotFoundException('Event not found');
        }

        if (!$event) {
            throw new NotFoundException('Event not found');
        }

        $heroFilename = $this->photosRepository->getFilename('dance_event_detail', 'hero_default');
        if ($heroFilename === null) {
            $heroFilename = 'DetailsPage/hero.png';
        }
        $heroImage = '/images/dance/' . $heroFilename;

        $gallery1 = $this->photosRepository->getFilename('dance_event_detail', 'gallery_default_1') ?? 'DetailsPage/2.png';
        $gallery2 = $this->photosRepository->getFilename('dance_event_detail', 'gallery_default_2') ?? 'DetailsPage/3.png';
        $gallery3 = $this->photosRepository->getFilename('dance_event_detail', 'gallery_default_3') ?? 'DetailsPage/4.png';
        $galleryImages = [$gallery1, $gallery2, $gallery3];

        $appSettings = $this->settingsRepository->getAll();
        $appConfig = require __DIR__ . '/../Config/app.php';
        $venueCoords = isset($appConfig['venue_coordinates']) ? $appConfig['venue_coordinates'] : [];
        $dayLabels = isset($appConfig['day_labels']) ? $appConfig['day_labels'] : [];

        $venueName = $event->venueName ?? '';
        $coord = isset($venueCoords[$venueName]) ? $venueCoords[$venueName] : [52.3813, 4.6368];
        $mapLat = (float) $coord[0];
        $mapLon = (float) $coord[1];

        $eventDay = $event->eventDay ?? 'friday';
        $formattedDate = isset($dayLabels[strtolower($eventDay)]) ? $dayLabels[strtolower($eventDay)] : ucfirst($eventDay);
        $startTime = $event->startTime ?? '14:00';
        $fullAddress = trim(($event->venueAddress ?? '') . ', ' . ($event->venueCity ?? ''));
        $mapQuery = urlencode($venueName . ' ' . $fullAddress);
        $locationDisplay = $venueName . ($event->venueCity ? ' — ' . $event->venueCity . ', Netherlands' : ' — Haarlem, Netherlands');

        // Event titles look like "Artist — Venue"; strip that so the page can show artists vs subtitle separately.
        if (preg_match('/^(.+?)\s*[–—-]\s*.+$/u', $event->title, $m)) {
            $artistsDisplay = str_replace([' / ', '/'], [', ', ', '], trim($m[1]));
        } else {
            $artistsDisplay = $event->title;
        }
        if (preg_match('/^.+?[–—-]\s*(.+)$/u', $event->title, $sm)) {
            $eventSubtitle = trim($sm[1]);
        } else {
            $eventSubtitle = $event->title;
        }

        $breadcrumbs = [
            ['label' => 'HOME', 'url' => '/'],
            ['label' => 'DANCE', 'url' => '/dance'],
            ['label' => strtoupper($venueName), 'url' => null],
        ];

        $viewModel = new EventDetailViewModel(
            $event,
            $heroImage,
            $galleryImages,
            $breadcrumbs,
            $appSettings,
            $formattedDate,
            $startTime,
            $locationDisplay,
            $mapLat,
            $mapLon,
            $mapQuery,
            $artistsDisplay,
            $eventSubtitle,
            ''
        );

        $ticketDetailsRepo = new TicketDetailsRepository();
        $ticketsRepo = new TicketsRepository();

        $viewModel->eventTickets = $ticketDetailsRepo->listByEventIdForPublic($event->id);
        $viewModel->danceDayPass = $ticketsRepo->getDanceDayPassForDay((string) $eventDay);
        $viewModel->danceAllAccessPass = $ticketsRepo->getDanceAllAccessPass();

        $this->attachTicketStockForEventDetail(
            $viewModel->eventTickets,
            $viewModel->danceDayPass,
            $viewModel->danceAllAccessPass
        );

        $viewModel->cartFlashSuccess = Session::getFlash('cart_success');
        $viewModel->cartFlashError = Session::getFlash('cart_error');

        require __DIR__ . '/../Views/Dance/EventDetail.php';
    }

    /**
     * @param list<array<string,mixed>> $eventTickets
     * @param array<string,mixed>|null $danceDayPass
     * @param array<string,mixed>|null $danceAllAccessPass
     */
    private function attachTicketStockForEventDetail(
        array &$eventTickets,
        ?array &$danceDayPass,
        ?array &$danceAllAccessPass
    ): void {
        $ids = [];
        foreach ($eventTickets as $t) {
            $ids[] = (int) ($t['ticket_details_id'] ?? 0);
        }
        if ($danceDayPass !== null) {
            $ids[] = (int) ($danceDayPass['ticket_details_id'] ?? 0);
        }
        if ($danceAllAccessPass !== null) {
            $ids[] = (int) ($danceAllAccessPass['ticket_details_id'] ?? 0);
        }
        $ids = array_values(array_filter($ids, static fn (int $id): bool => $id > 0));

        $stock = $ids !== []
            ? (new TicketAvailabilityService(new CartRepository(), new TicketRepository()))
                ->stockUiByTicketDetailsIds($ids)
            : [];

        $neutral = [
            'sold_out' => false,
            'nearly' => false,
            'low_stock' => false,
            'remaining' => null,
        ];

        foreach ($eventTickets as &$t) {
            $tid = (int) ($t['ticket_details_id'] ?? 0);
            $t['stock'] = $stock[$tid] ?? $neutral;
        }
        unset($t);

        if ($danceDayPass !== null) {
            $tid = (int) ($danceDayPass['ticket_details_id'] ?? 0);
            $danceDayPass['stock'] = $stock[$tid] ?? $neutral;
        }
        if ($danceAllAccessPass !== null) {
            $tid = (int) ($danceAllAccessPass['ticket_details_id'] ?? 0);
            $danceAllAccessPass['stock'] = $stock[$tid] ?? $neutral;
        }
    }
}
