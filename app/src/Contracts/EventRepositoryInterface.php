<?php

namespace App\Contracts;

use App\Models\Event;

interface EventRepositoryInterface
{
    /** @return Event[] */
    public function getAll(): array;

    /** @return Event[] */
    public function getByCategory(string $eventTypeName): array;

    /** @return Event[] */
    public function getByCategoryAndDay(string $eventTypeName, string $eventDay): array;
}
