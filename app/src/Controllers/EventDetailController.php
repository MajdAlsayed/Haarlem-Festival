<?php

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\CartRepository;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\PhotosRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketDetailsRepository;
use App\Repositories\TicketRepository;
use App\Repositories\TicketsRepository;
use App\Services\EventService;
use App\Services\TicketAvailabilityService;
use App\ViewModels\EventDetailViewModel;
use App\Exceptions\NotFoundException;

/** /dance/event/{id} */
class EventDetailController
{
    private EventService $eventService;
    private PhotosRepository $photosRepository;
    private SettingsRepository $settingsRepository;
    private DanceSettingsRepository $danceSettings;

    public function __construct()
    {
        $this->eventService = new EventService(new \App\Repositories\EventRepository());
        $this->photosRepository = new PhotosRepository();
        $this->settingsRepository = new SettingsRepository();
        $this->danceSettings = new DanceSettingsRepository();
    }

    public function show(int $eventId): void
    {
        try {
            $event = $this->eventService->getById($eventId);
        } catch (\Throwable $e) {
            // Don’t leak DB/errors to visitors; router shows generic 404.
            throw new NotFoundException('Event not found');
        }

        if (!$event) {
            throw new NotFoundException('Event not found');
        }

        // site_settings (global); dance_settings + dance.php (dance-only, incl. map + breadcrumbs).
        $app = $this->settingsRepository->getAll();
        $d = $this->danceSettings->getMergedWithConfig();

        // photos.context in DB; slot keys fixed in PhotosRepository/CMS, not configurable here.
        $ctx = $d['event_detail_photos_context'];
        $gf = $d['event_detail_gallery_fallbacks'];
        if (!is_array($gf)) {
            // Last-resort if dance_settings JSON is corrupt; matches shipped assets under public/images/dance/.
            $gf = ['DetailsPage/2.png', 'DetailsPage/3.png', 'DetailsPage/4.png'];
        }
        $heroFile = $this->photosRepository->getFilename($ctx, 'hero_default') ?? $d['event_detail_hero_fallback'];
        // Web path; files live under app/public/images/dance/ (nginx docroot).
        $heroImage = '/images/dance/' . $heroFile;
        $galleryImages = [
            $this->photosRepository->getFilename($ctx, 'gallery_default_1') ?? $gf[0],
            $this->photosRepository->getFilename($ctx, 'gallery_default_2') ?? $gf[1],
            $this->photosRepository->getFilename($ctx, 'gallery_default_3') ?? $gf[2],
        ];

        $venueName = $event->venueName ?? '';
        [$mapLat, $mapLon] = $this->mapLatLon($venueName, $d);

        $eventDay = $event->eventDay ?? $d['default_event_day'];
        $dayKey = strtolower((string) $eventDay);
        $dayLabels = is_array($d['day_labels'] ?? null) ? $d['day_labels'] : [];
        $formattedDate = $dayLabels[$dayKey] ?? ucfirst((string) $eventDay);
        // Shared site default (site_settings / app.php), not dance-only.
        $startTime = $event->startTime ?? $app['default_event_time'];

        // Google/open map search string; trailing comma ok if address empty.
        $fullAddress = trim(($event->venueAddress ?? '') . ', ' . ($event->venueCity ?? ''));
        $mapQuery = urlencode($venueName . ' ' . $fullAddress);

        $country = $d['event_detail_venue_country'];
        $cityFallback = $app['default_venue_city'];
        // Em dash matches design copy; country/city from CMS/settings.
        $locationDisplay = $event->venueCity
            ? $venueName . ' — ' . $event->venueCity . ', ' . $country
            : $venueName . ' — ' . $cityFallback . ', ' . $country;

        [$artistsDisplay, $eventSubtitle] = $this->splitEventTitle($event->title);

        $breadcrumbs = [
            ['label' => $d['breadcrumb_home_label'], 'url' => $app['home_path']],
            ['label' => $d['breadcrumb_dance_label'], 'url' => $d['event_detail_list_path']],
            // ALL CAPS: design spec for venue crumb on this page.
            ['label' => strtoupper($venueName), 'url' => null],
        ];

        $viewModel = new EventDetailViewModel(
            $event,
            $heroImage,
            $galleryImages,
            $breadcrumbs,
            $app,
            $formattedDate,
            $startTime,
            $locationDisplay,
            $mapLat,
            $mapLon,
            $mapQuery,
            $artistsDisplay,
            $eventSubtitle,
            '' // pageTitle: empty → EventDetailViewModel uses venue + event title for browser tab.
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

        // Keys must stay in sync with CartController flash names.
        $viewModel->cartFlashSuccess = Session::getFlash('cart_success');
        $viewModel->cartFlashError = Session::getFlash('cart_error');

        require __DIR__ . '/../Views/Dance/EventDetail.php';
    }

    /**
     * @param array<string, mixed> $d merged dance_settings + dance.php
     * @return array{0: float, 1: float}
     */
    private function mapLatLon(string $venueName, array $d): array
    {
        $byVenue = $d['venue_coordinates'];
        if (!is_array($byVenue)) {
            $byVenue = [];
        }
        $fallback = $d['default_map_coordinates'];
        if (!is_array($fallback)) {
            // Jopenkerk area, same as default in dance.php / migration if DB row missing/bad.
            $fallback = [52.3813, 4.6368];
        }
        $pair = $byVenue[$venueName] ?? $fallback;

        return [(float) $pair[0], (float) $pair[1]];
    }

    /** @return array{0: string, 1: string} */
    private function splitEventTitle(string $title): array
    {
        // Titles stored as "Artist — Venue" (en dash, em dash, or ASCII hyphen).
        if (preg_match('/^(.+?)\s*[–—-]\s*.+$/u', $title, $m)) {
            // DB often uses "A / B" for multi-artist; UI wants comma list.
            $artists = str_replace([' / ', '/'], [', ', ', '], trim($m[1]));
        } else {
            $artists = $title;
        }
        if (preg_match('/^.+?[–—-]\s*(.+)$/u', $title, $sm)) {
            $subtitle = trim($sm[1]);
        } else {
            $subtitle = $title;
        }

        return [$artists, $subtitle];
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
        $ids = array_values(array_filter($ids, fn (int $id) => $id > 0));

        $stock = $ids !== []
            ? (new TicketAvailabilityService(new CartRepository(), new TicketRepository()))
                ->stockUiByTicketDetailsIds($ids)
            : [];

        // Shape expected by EventDetail.php partials (no fetch if no ticket ids).
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
