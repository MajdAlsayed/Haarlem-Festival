<?php

namespace App\Controllers;

use App\Contracts\ServiceInterface\EventServiceInterface;
use App\Repositories\EventRepository;
use App\Services\ArtistService;
use App\Services\EventService;
use App\ViewModels\DanceViewModel;

class DanceController
{
    private EventServiceInterface $eventService;
    private ArtistService $artistService;

    public function __construct()
    {
        $this->eventService = new EventService(new EventRepository());
        $this->artistService = new ArtistService(new \App\Repositories\ArtistsRepository(), new EventRepository());
    }

    public function index(): void
    {
        $events = $this->eventService->getByCategory('dance');
        $fridayEvents = $this->eventService->getByCategoryAndDay('dance', 'friday');
        // Figma order: Lichtfabriek, Slachthuis, Jopenkerk, XO, Puncher (venue_id 4,7,5,8,9)
        $fridayVenueOrder = [4 => 0, 7 => 1, 5 => 2, 8 => 3, 9 => 4];
        usort($fridayEvents, function ($a, $b) use ($fridayVenueOrder) {
            $posA = $fridayVenueOrder[$a->venueId] ?? 99; // unknown venue last
            $posB = $fridayVenueOrder[$b->venueId] ?? 99;
            return $posA <=> $posB;
        });
        $saturdayEvents = $this->eventService->getByCategoryAndDay('dance', 'saturday');
        // Figma order: Caprera, Jopenkerk, Slachthuis (venue_id 6,5,7); within Slachthuis by time
        $saturdayVenueOrder = [6 => 0, 5 => 1, 7 => 2];
        usort($saturdayEvents, function ($a, $b) use ($saturdayVenueOrder) {
            $posA = $saturdayVenueOrder[$a->venueId] ?? 99;
            $posB = $saturdayVenueOrder[$b->venueId] ?? 99;
            if ($posA !== $posB) return $posA <=> $posB;
            return strcmp($a->startTime ?? '', $b->startTime ?? ''); // same venue: by time
        });
        $sundayEvents = $this->eventService->getByCategoryAndDay('dance', 'sunday');
        // Figma order: Caprera, Jopenkerk, XO, Slachthuis (venue_id 6,5,8,7)
        $sundayVenueOrder = [6 => 0, 5 => 1, 8 => 2, 7 => 3];
        usort($sundayEvents, function ($a, $b) use ($sundayVenueOrder) {
            $posA = $sundayVenueOrder[$a->venueId] ?? 99;
            $posB = $sundayVenueOrder[$b->venueId] ?? 99;
            return $posA <=> $posB;
        });

        $artists = $this->artistService->getAllOrdered();
        $viewModel = new DanceViewModel($events, $fridayEvents, $saturdayEvents, $sundayEvents, $artists);

        require __DIR__ . '/../Views/Dance/Index.php';
    }
}
