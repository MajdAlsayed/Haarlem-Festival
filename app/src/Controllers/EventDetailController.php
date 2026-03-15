<?php

namespace App\Controllers;

use App\Repositories\PhotosRepository;
use App\Repositories\SettingsRepository;
use App\Services\EventService;
use App\ViewModels\EventDetailViewModel;
use App\Exceptions\NotFoundException;

/**
 * Dance event detail page. Controller gets event and images, builds view model, loads view.
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

        require __DIR__ . '/../Views/Dance/EventDetail.php';
    }
}
