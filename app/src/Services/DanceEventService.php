<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\DanceEventServiceInterface;
use App\Exceptions\NotFoundException;
use App\Models\Event;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\PhotosRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketDetailsRepository;
use App\Repositories\TicketsRepository;
use App\ViewModels\EventDetailViewModel;

// builds the data for a single dance event page (/dance/event/{id})
class DanceEventService implements DanceEventServiceInterface
{
    private EventService $eventService;
    private PhotosRepository $photosRepository;
    private SettingsRepository $settingsRepository;
    private DanceSettingsRepository $danceSettingsRepository;
    private TicketDetailsRepository $ticketDetailsRepository;
    private TicketsRepository $ticketsRepository;
    private TicketAvailabilityService $ticketAvailabilityService;

    public function __construct(
        EventService $eventService,
        PhotosRepository $photosRepository,
        SettingsRepository $settingsRepository,
        DanceSettingsRepository $danceSettingsRepository,
        TicketDetailsRepository $ticketDetailsRepository,
        TicketsRepository $ticketsRepository,
        TicketAvailabilityService $ticketAvailabilityService
    ) {
        $this->eventService = $eventService;
        $this->photosRepository = $photosRepository;
        $this->settingsRepository = $settingsRepository;
        $this->danceSettingsRepository = $danceSettingsRepository;
        $this->ticketDetailsRepository = $ticketDetailsRepository;
        $this->ticketsRepository = $ticketsRepository;
        $this->ticketAvailabilityService = $ticketAvailabilityService;
    }

    // load the event (404 if it's gone) and pack everything the detail page needs into one view model
    public function buildDetailViewModel(int $eventId): EventDetailViewModel
    {
        $event = $this->eventService->getById($eventId);
        if (!$event) {
            throw new NotFoundException('Event not found');
        }

        $app = $this->settingsRepository->getAll();
        $dance = $this->danceSettingsRepository->getMergedWithConfig();

        $venueName = $event->venueName ?? '';
        $eventDay = $event->eventDay ?? $dance['default_event_day'];

        // work everything out first, then hand it to the view model
        $heroImage = '/images/dance/' . $this->resolveHeroFile($dance);
        $galleryImages = $this->resolveGalleryImages($dance);
        $breadcrumbs = $this->buildBreadcrumbs($venueName, $dance, $app);
        $formattedDate = $this->formatEventDate($eventDay, $dance);
        $startTime = $event->startTime ?? $app['default_event_time'];
        $location = $this->buildLocationDisplay($event, $venueName, $dance, $app);
        $mapQuery = $this->buildMapQuery($event, $venueName);
        [$mapLat, $mapLon] = $this->mapLatLon($venueName, $dance);
        [$artistsDisplay, $eventSubtitle] = $this->splitEventTitle($event->title);

        // page title is left out on purpose → the view model falls back to venue + event title
        $vm = new EventDetailViewModel(
            event: $event,
            heroImage: $heroImage,
            galleryImages: $galleryImages,
            breadcrumbs: $breadcrumbs,
            appSettings: $app,
            formattedDate: $formattedDate,
            startTime: $startTime,
            locationDisplay: $location,
            mapLat: $mapLat,
            mapLon: $mapLon,
            mapQuery: $mapQuery,
            artistsDisplay: $artistsDisplay,
            eventSubtitle: $eventSubtitle
        );

        $vm->eventTickets = $this->ticketDetailsRepository->listByEventIdForPublic($event->id);
        $vm->danceDayPass = $this->ticketsRepository->getDanceDayPassForDay((string) $eventDay);
        $vm->danceAllAccessPass = $this->ticketsRepository->getDanceAllAccessPass();

        $this->attachTicketStock($vm->eventTickets, $vm->danceDayPass, $vm->danceAllAccessPass);

        return $vm;
    }

    // hero image filename from the cms photos, or the fallback from the settings
    private function resolveHeroFile(array $dance): string
    {
        $context = $dance['event_detail_photos_context'];
        $heroFile = $this->photosRepository->getFilename($context, 'hero_default');

        return $heroFile ?? $dance['event_detail_hero_fallback'];
    }

    // three gallery image paths from the cms photos, falling back to the shipped assets
    /** @return string[] */
    private function resolveGalleryImages(array $dance): array
    {
        $context = $dance['event_detail_photos_context'];
        $fallbacks = $dance['event_detail_gallery_fallbacks'];
        if (!is_array($fallbacks)) {
            $fallbacks = ['DetailsPage/2.png', 'DetailsPage/3.png', 'DetailsPage/4.png'];
        }

        return [
            $this->photosRepository->getFilename($context, 'gallery_default_1') ?? $fallbacks[0],
            $this->photosRepository->getFilename($context, 'gallery_default_2') ?? $fallbacks[1],
            $this->photosRepository->getFilename($context, 'gallery_default_3') ?? $fallbacks[2],
        ];
    }

    private function buildBreadcrumbs(string $venueName, array $dance, array $app): array
    {
        return [
            ['label' => $dance['breadcrumb_home_label'], 'url' => $app['home_path']],
            ['label' => $dance['breadcrumb_dance_label'], 'url' => $dance['event_detail_list_path']],
            // venue crumb is all caps on purpose, that's what the design asks for
            ['label' => strtoupper($venueName), 'url' => null],
        ];
    }

    // day label from the cms (like "Friday 24 May"), or just the capitalised day name
    private function formatEventDate(string $eventDay, array $dance): string
    {
        $dayKey = strtolower($eventDay);

        $dayLabels = [];
        if (isset($dance['day_labels']) && is_array($dance['day_labels'])) {
            $dayLabels = $dance['day_labels'];
        }

        return $dayLabels[$dayKey] ?? ucfirst($eventDay);
    }

    private function buildLocationDisplay(Event $event, string $venueName, array $dance, array $app): string
    {
        $country = $dance['event_detail_venue_country'];

        // use the event's city, or the site default when the event has none
        $city = $event->venueCity;
        if (!$city) {
            $city = $app['default_venue_city'];
        }

        // shows like "Jopenkerk — Haarlem, Netherlands"
        return sprintf('%s — %s, %s', $venueName, $city, $country);
    }

    // build the map search string, like "Jopenkerk, Gedempte Voldersgracht, Haarlem"
    private function buildMapQuery(Event $event, string $venueName): string
    {
        // drop any empty parts, then join what's left with commas
        $parts = array_filter([$venueName, $event->venueAddress, $event->venueCity]);

        return urlencode(implode(', ', $parts));
    }

    // look up the venue coordinates, or fall back to the default ones
    /** @return float[] */
    private function mapLatLon(string $venueName, array $dance): array
    {
        $byVenue = is_array($dance['venue_coordinates']) ? $dance['venue_coordinates'] : [];
        $fallback = $dance['default_map_coordinates'];
        if (!is_array($fallback)) {
            // jopenkerk area, same default as dance.php / the migration
            $fallback = [52.3813, 4.6368];
        }

        $pair = $byVenue[$venueName] ?? $fallback;

        return [(float) $pair[0], (float) $pair[1]];
    }

    // titles look like "Artist — Venue", so split them into the artists part and the subtitle
    /** @return string[] */
    private function splitEventTitle(string $title): array
    {
        // split on the dash (–, — or -) with any spaces around it, max 2 parts
        $parts = preg_split('/\s*[–—-]\s*/u', $title, 2);

        // no dash in the title → just use the whole thing for both
        if ($parts === false || count($parts) < 2) {
            return [$title, $title];
        }

        // the db sometimes lists artists as "A / B", but we want "A, B"
        $names = array_map('trim', explode('/', $parts[0]));
        $artists = implode(', ', $names);
        $subtitle = trim($parts[1]);

        return [$artists, $subtitle];
    }

    // add a stock status to each ticket so the view can show the "sold out" / "nearly" badges
    private function attachTicketStock(array &$eventTickets, ?array &$danceDayPass, ?array &$danceAllAccessPass): void
    {
        $ids = [];
        foreach ($eventTickets as $ticket) {
            $ids[] = $this->ticketDetailsId($ticket);
        }
        if ($danceDayPass !== null) {
            $ids[] = $this->ticketDetailsId($danceDayPass);
        }
        if ($danceAllAccessPass !== null) {
            $ids[] = $this->ticketDetailsId($danceAllAccessPass);
        }
        $ids = array_values(array_filter($ids, fn (int $id) => $id > 0));

        $stock = $ids !== [] ? $this->ticketAvailabilityService->stockUiByTicketDetailsIds($ids) : [];

        foreach ($eventTickets as &$ticket) {
            $ticket['stock'] = $this->stockFor($ticket, $stock);
        }
        unset($ticket);

        if ($danceDayPass !== null) {
            $danceDayPass['stock'] = $this->stockFor($danceDayPass, $stock);
        }
        if ($danceAllAccessPass !== null) {
            $danceAllAccessPass['stock'] = $this->stockFor($danceAllAccessPass, $stock);
        }
    }

    // the ticket_details_id for a ticket row, or 0 when it's not there
    private function ticketDetailsId(array $ticket): int
    {
        return (int) ($ticket['ticket_details_id'] ?? 0);
    }

    // the stock info for a ticket, or a neutral "nothing special" status when we have none
    private function stockFor(array $ticket, array $stock): array
    {
        $ticketId = $this->ticketDetailsId($ticket);

        return $stock[$ticketId] ?? [
            'sold_out' => false,
            'nearly' => false,
            'low_stock' => false,
            'remaining' => null,
        ];
    }
}
