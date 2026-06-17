<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\TicketsCatalogServiceInterface;
use App\Repositories\TicketsRepository;

// dance passes + shop intro from ticket_details catalog helpers
final class TicketsCatalogService implements TicketsCatalogServiceInterface
{
    public function __construct(
        private TicketsRepository $ticketsRepository,
    ) {
    }

    public function getDanceDayPassForDay(string $eventDay): ?array
    {
        return $this->ticketsRepository->getDanceDayPassForDay($eventDay);
    }

    public function getDanceAllAccessPass(): ?array
    {
        return $this->ticketsRepository->getDanceAllAccessPass();
    }
}
