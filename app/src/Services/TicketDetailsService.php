<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\TicketDetailsServiceInterface;
use App\Repositories\TicketDetailsRepository;

// shop catalog rows in ticket_details
final class TicketDetailsService implements TicketDetailsServiceInterface
{
    public function __construct(
        private TicketDetailsRepository $ticketDetailsRepository,
    ) {
    }

    public function listByEventIdForPublic(int $eventId): array
    {
        return $this->ticketDetailsRepository->listByEventIdForPublic($eventId);
    }
}
