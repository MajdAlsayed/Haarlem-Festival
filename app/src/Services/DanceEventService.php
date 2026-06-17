<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\DanceEventServiceInterface;
use App\Contracts\ServiceInterface\DanceSettingsServiceInterface;
use App\Contracts\ServiceInterface\EventServiceInterface;
use App\Contracts\ServiceInterface\PhotosServiceInterface;
use App\Contracts\ServiceInterface\SettingsServiceInterface;
use App\Contracts\ServiceInterface\TicketDetailsServiceInterface;
use App\Contracts\ServiceInterface\TicketsCatalogServiceInterface;
use App\Core\Csrf;
use App\Exceptions\NotFoundException;
use App\Models\Event;
use App\ViewModels\EventDetailViewModel;

// /dance/{id} — event detail, ticket cards, stock for buy buttons
class DanceEventService implements DanceEventServiceInterface
{
    private const IMAGE_BASE_PATH = '/images/dance/';
    private const DEFAULT_MAP_COORDINATES = [52.3813, 4.6368];
    private const DEFAULT_GALLERY_IMAGES = ['DetailsPage/2.png', 'DetailsPage/3.png', 'DetailsPage/4.png'];
    private const TICKET_CARD_CLASS = 'event-detail-ticket-card event-detail-ticket-card--figma event-detail-ticket-card--flex';

    public function __construct(
        private EventServiceInterface $eventService,
        private PhotosServiceInterface $photosService,
        private SettingsServiceInterface $settingsService,
        private DanceSettingsServiceInterface $danceSettingsService,
        private TicketDetailsServiceInterface $ticketDetailsService,
        private TicketsCatalogServiceInterface $ticketsCatalogService,
        private TicketAvailabilityService $ticketAvailabilityService,
    ) {
    }

    // used by EventDetailController
    public function buildDetailViewModel(int $eventId): EventDetailViewModel
    {
        return EventDetailViewModel::fromPageData($this->getEventDetailData($eventId));
    }

    // event info + ticket cards + stock for buy buttons
    public function getEventDetailData(int $eventId): array
    {
        $event = $this->loadEvent($eventId);
        $app = $this->settingsService->getAll();
        $dance = $this->danceSettingsService->getMergedWithConfig();
        $venueName = (string) $event->venueName;
        $eventDay = (string) ($event->eventDay ?? $dance['default_event_day']);
        $eventInfo = $this->loadEventInfoData($event, $dance, $app, $eventDay, $venueName);

        return [
            'event' => $event,
            'appSettings' => $app,
            'breadcrumbs' => $this->buildBreadcrumbs($venueName, $dance, $app),
            'hero' => $this->loadHeroData($dance),
            'eventInfo' => $eventInfo,
            'display' => $this->loadDisplayData($event, $eventInfo),
            'map' => $this->loadMapData($event, $venueName, $dance),
            'tickets' => $this->loadTicketsData($event, $eventDay),
        ];
    }

    private function loadEvent(int $eventId): Event
    {
        $event = $this->eventService->getById($eventId);
        if (!$event) {
            throw new NotFoundException('Event not found');
        }

        return $event;
    }

    private function loadHeroData(array $dance): array
    {
        return [
            'image' => self::IMAGE_BASE_PATH . $this->resolveHeroFile($dance),
            'galleryImages' => $this->resolveGalleryImages($dance),
        ];
    }

    private function loadEventInfoData(
        Event $event,
        array $dance,
        array $app,
        string $eventDay,
        string $venueName,
    ): array {
        [$artistsDisplay, $eventSubtitle] = $this->splitEventTitle($event->title);

        return [
            'formattedDate' => $this->formatEventDate($eventDay, $dance),
            'startTime' => (string) ($event->startTime ?? $app['default_event_time']),
            'locationDisplay' => $this->buildLocationDisplay($event, $venueName, $dance, $app),
            'artistsDisplay' => $artistsDisplay,
            'eventSubtitle' => $eventSubtitle,
        ];
    }

    private function loadMapData(Event $event, string $venueName, array $dance): array
    {
        [$mapLat, $mapLon] = $this->mapLatLon($venueName, $dance);

        return [
            'lat' => $mapLat,
            'lon' => $mapLon,
            'query' => $this->buildMapQuery($event, $venueName),
        ];
    }

    private function loadTicketsData(Event $event, string $eventDay): array
    {
        $eventTickets = $this->ticketDetailsService->listByEventIdForPublic($event->id);
        $danceDayPass = $this->ticketsCatalogService->getDanceDayPassForDay($eventDay);
        $danceAllAccessPass = $this->ticketsCatalogService->getDanceAllAccessPass();

        $this->attachTicketStock($eventTickets, $danceDayPass, $danceAllAccessPass);

        return [
            'eventTickets' => $eventTickets,
            'danceDayPass' => $danceDayPass,
            'danceAllAccessPass' => $danceAllAccessPass,
            'cart' => $this->loadCartData($event),
            'cards' => $this->loadTicketCards($eventTickets, $danceDayPass, $danceAllAccessPass),
        ];
    }

    private function loadDisplayData(Event $event, array $eventInfo): array
    {
        $formattedDate = (string) $eventInfo['formattedDate'];
        $startTime = (string) $eventInfo['startTime'];
        $venueName = (string) $event->venueName;
        $venueCity = (string) $event->venueCity;

        $dateTimeLine = $startTime;
        if ($formattedDate !== '') {
            $dateTimeLine = $formattedDate . ' • ' . $startTime;
        }

        $venueLine = $venueName;
        if ($venueCity !== '') {
            $venueLine .= ', ' . $venueCity;
        }

        $pageHeroTitle = (string) $event->title;
        if ($venueName !== '') {
            $pageHeroTitle = $venueName;
        }

        return [
            'dateTimeLine' => $dateTimeLine,
            'venueLine' => $venueLine,
            'pageHeroTitle' => $pageHeroTitle,
            'ticketsFigmaTitle' => 'Ticket for ' . $event->title . ' in ' . $venueName,
        ];
    }

    private function loadCartData(Event $event): array
    {
        $csrf = Csrf::peek('cart') ?? Csrf::token('cart');

        return [
            'return' => '/dance/event/' . (int) $event->id . '#tickets',
            'csrf' => $csrf,
        ];
    }

    private function loadTicketCards(array $eventTickets, ?array $dayPass, ?array $festivalPass): array
    {
        $hasEventTickets = $eventTickets !== [];
        $hasDayPass = $dayPass !== null;

        return [
            'event' => $this->buildEventTicketCards($eventTickets),
            'dayPass' => $dayPass !== null ? $this->buildDayPassCard($dayPass) : null,
            'festival' => $festivalPass !== null ? $this->buildFestivalPassCard($festivalPass) : null,
            'standardCellClass' => $this->ticketCellClass($hasEventTickets && !$hasDayPass),
            'dayCellClass' => $this->ticketCellClass(!$hasEventTickets && $hasDayPass),
        ];
    }

    private function ticketCellClass(bool $spanTop): string
    {
        $class = 'event-detail-tickets-cell';

        return $spanTop ? $class . ' event-detail-tickets-cell--span-top' : $class;
    }

    private function buildEventTicketCards(array $tickets): array
    {
        $total = count($tickets);
        $cards = [];

        foreach ($tickets as $ticket) {
            $cards[] = $this->buildEventTicketCard($ticket, $total);
        }

        return $cards;
    }

    private function buildEventTicketCard(array $ticket, int $totalCount): array
    {
        $name = $this->ticketText($ticket, 'name') ?: 'Ticket';
        $isVip = $this->isVipTicket($name);
        $card = $this->baseCard($ticket);

        $card['title'] = ($totalCount === 1 && !$isVip) ? 'Standard Ticket' : $name;
        $card['cardClass'] = self::TICKET_CARD_CLASS . ($isVip ? ' vip' : '');
        $card['badge'] = $isVip ? 'VIP' : '';
        $card['featureColumns'] = $this->featureColumns($this->ticketText($ticket, 'description'), false);
        $card['featuresWrapped'] = false;
        $card['fallbackFeature'] = 'Access to this event';
        $card['meta'] = '';
        $card['buttonClass'] = 'btn btn--light btn--block';
        $card['canBuy'] = $card['tdId'] > 0 && $card['stockState'] !== 'soldout';

        return $card;
    }

    private function buildDayPassCard(array $pass): array
    {
        $card = $this->baseCard($pass);
        $name = $this->ticketText($pass, 'name') ?: 'Day Pass';
        $desc = $this->ticketText($pass, 'description');

        $card['title'] = $name;
        $card['cardClass'] = self::TICKET_CARD_CLASS . ' event-detail-ticket-card--pass';
        $card['badge'] = '';
        $card['featureColumns'] = $this->featureColumns($desc, false);
        $card['featuresWrapped'] = false;
        $card['fallbackFeature'] = '';
        $card['meta'] = $this->dayPassMeta($pass);
        $card['buttonClass'] = 'btn btn--light';
        $card['canBuy'] = $card['tdId'] > 0 && $card['stockState'] !== 'soldout' && empty($pass['is_free']);

        return $card;
    }

    private function buildFestivalPassCard(array $pass): array
    {
        $card = $this->baseCard($pass);
        $name = $this->ticketText($pass, 'name') ?: 'All-Access Pass';
        $desc = $this->ticketText($pass, 'description');

        $card['title'] = $name;
        $card['cardClass'] = self::TICKET_CARD_CLASS . ' event-detail-ticket-card--festival';
        $card['badge'] = 'BEST VALUE';
        $card['featureColumns'] = $this->featureColumns($desc, true);
        $card['featuresWrapped'] = true;
        $card['fallbackFeature'] = '';
        $card['meta'] = $this->ticketText($pass, 'schedule_display');
        $card['buttonClass'] = 'btn btn--light';
        $card['canBuy'] = $card['tdId'] > 0 && $card['stockState'] !== 'soldout' && empty($pass['is_free']);

        return $card;
    }

    private function isVipTicket(string $name): bool
    {
        return stripos($name, 'VIP') !== false;
    }

    private function baseCard(array $ticket): array
    {
        $stock = $this->ticketStock($ticket);
        $isFree = !empty($ticket['is_free']);

        $priceRaw = isset($ticket['price']) ? $ticket['price'] : null;
        $remaining = isset($stock['remaining']) ? (int) $stock['remaining'] : 0;

        return [
            'tdId' => $this->ticketDetailsId($ticket),
            'priceLabel' => $this->priceLabel($priceRaw, $isFree),
            'stockState' => $this->stockState($stock),
            'remaining' => $remaining,
        ];
    }

    private function ticketStock(array $ticket): array
    {
        if (isset($ticket['stock']) && is_array($ticket['stock'])) {
            return $ticket['stock'];
        }

        return [];
    }

    private function stockState(array $stock): string
    {
        if (!empty($stock['sold_out'])) {
            return 'soldout';
        }
        if (!empty($stock['nearly'])) {
            return 'nearly';
        }
        if (!empty($stock['low_stock'])) {
            return 'low';
        }

        return '';
    }

    private function priceLabel(mixed $price, bool $isFree): string
    {
        if ($isFree) {
            return 'Free';
        }
        $amount = is_numeric($price) ? (float) $price : 0.0;

        return '€ ' . number_format($amount, 2, ',', '.');
    }

    private function featureColumns(string $desc, bool $twoColumns): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($desc));
        $lines = array_values(array_filter(array_map('trim', $lines), static fn ($line) => $line !== ''));
        if ($lines === []) {
            return [];
        }
        if (!$twoColumns) {
            return [$lines];
        }

        $mid = (int) ceil(count($lines) / 2);
        $first = array_slice($lines, 0, $mid);
        $second = array_slice($lines, $mid);
        if ($second === []) {
            return [$first];
        }

        return [$first, $second];
    }

    private function dayPassMeta(array $pass): string
    {
        $parts = [];
        $day = $this->ticketText($pass, 'pass_day');
        if ($day !== '') {
            $parts[] = ucfirst($day) . ' pass';
        }
        $time = $this->ticketText($pass, 'pass_time');
        if ($time !== '') {
            $parts[] = $time;
        }

        return implode(' · ', $parts);
    }

    private function ticketText(array $ticket, string $key): string
    {
        if (isset($ticket[$key]) && $ticket[$key] !== null) {
            return (string) $ticket[$key];
        }

        return '';
    }

    private function resolveHeroFile(array $dance): string
    {
        $context = $dance['event_detail_photos_context'];
        $heroFile = $this->photosService->getFilename($context, 'hero_default');

        return $heroFile ?? $dance['event_detail_hero_fallback'];
    }

    private function resolveGalleryImages(array $dance): array
    {
        $context = $dance['event_detail_photos_context'];
        $fallbacks = $dance['event_detail_gallery_fallbacks'];
        if (!is_array($fallbacks)) {
            $fallbacks = self::DEFAULT_GALLERY_IMAGES;
        }

        return [
            $this->photosService->getFilename($context, 'gallery_default_1') ?? $fallbacks[0],
            $this->photosService->getFilename($context, 'gallery_default_2') ?? $fallbacks[1],
            $this->photosService->getFilename($context, 'gallery_default_3') ?? $fallbacks[2],
        ];
    }

    private function buildBreadcrumbs(string $venueName, array $dance, array $app): array
    {
        return [
            ['label' => $dance['breadcrumb_home_label'], 'url' => $app['home_path']],
            ['label' => $dance['breadcrumb_dance_label'], 'url' => $dance['event_detail_list_path']],
            ['label' => strtoupper($venueName), 'url' => null],
        ];
    }

    private function formatEventDate(string $eventDay, array $dance): string
    {
        $dayKey = strtolower($eventDay);
        $dayLabels = isset($dance['day_labels']) && is_array($dance['day_labels'])
            ? $dance['day_labels']
            : [];

        return $dayLabels[$dayKey] ?? ucfirst($eventDay);
    }

    private function buildLocationDisplay(Event $event, string $venueName, array $dance, array $app): string
    {
        $country = $dance['event_detail_venue_country'];
        $city = $event->venueCity ?: $app['default_venue_city'];

        return sprintf('%s — %s, %s', $venueName, $city, $country);
    }

    private function buildMapQuery(Event $event, string $venueName): string
    {
        $parts = array_filter([$venueName, $event->venueAddress, $event->venueCity]);

        return urlencode(implode(', ', $parts));
    }

    private function mapLatLon(string $venueName, array $dance): array
    {
        $byVenue = is_array($dance['venue_coordinates']) ? $dance['venue_coordinates'] : [];
        $fallback = $dance['default_map_coordinates'];
        if (!is_array($fallback)) {
            $fallback = self::DEFAULT_MAP_COORDINATES;
        }

        $pair = $byVenue[$venueName] ?? $fallback;

        return [(float) $pair[0], (float) $pair[1]];
    }

    private function splitEventTitle(string $title): array
    {
        $parts = preg_split('/\s*[–—-]\s*/u', $title, 2);
        if ($parts === false || count($parts) < 2) {
            return [$title, $title];
        }

        $names = array_map('trim', explode('/', $parts[0]));
        $artists = implode(', ', $names);

        return [$artists, trim($parts[1])];
    }

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

    private function ticketDetailsId(array $ticket): int
    {
        return (int) ($ticket['ticket_details_id'] ?? 0);
    }

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
