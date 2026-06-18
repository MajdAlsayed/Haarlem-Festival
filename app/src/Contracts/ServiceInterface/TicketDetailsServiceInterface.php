<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

interface TicketDetailsServiceInterface
{
    public function listByEventIdForPublic(int $eventId): array;
}
