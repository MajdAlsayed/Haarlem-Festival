<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

interface TicketsCatalogServiceInterface
{
    public function getDanceDayPassForDay(string $eventDay): ?array;

    public function getDanceAllAccessPass(): ?array;
}
