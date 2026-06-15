<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\CartRepository;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\EventRepository;
use App\Repositories\PhotosRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketDetailsRepository;
use App\Repositories\TicketRepository;
use App\Repositories\TicketsRepository;
use App\Services\DanceEventService;
use App\Services\EventService;
use App\Services\TicketAvailabilityService;

final class EventDetailController
{
    private DanceEventService $danceEventService;

    public function __construct()
    {
        $this->danceEventService = new DanceEventService(
            new EventService(new EventRepository()),
            new PhotosRepository(),
            new SettingsRepository(),
            new DanceSettingsRepository(),
            new TicketDetailsRepository(),
            new TicketsRepository(),
            new TicketAvailabilityService(new CartRepository(), new TicketRepository())
        );
    }

    public function show(int $eventId): void
    {
        $vm = $this->danceEventService->buildDetailViewModel($eventId);

        // Cart flash messages live in the session, so they are read here in the controller.
        $vm->cartFlashSuccess = Session::getFlash('cart_success');
        $vm->cartFlashError = Session::getFlash('cart_error');

        require __DIR__ . '/../Views/Dance/EventDetail.php';
    }
}
