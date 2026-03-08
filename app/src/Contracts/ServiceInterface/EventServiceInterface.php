<?php

namespace App\Contracts\ServiceInterface;

use App\Models\Event;

interface EventServiceInterface
{
    /** @return Event[] */
    public function getAll(): array;

    /** @return Event[] */
    public function getByCategory(string $eventTypeName): array;

    /** @return Event[] */
    public function getByCategoryAndDay(string $eventTypeName, string $eventDay): array;

    public function getById(int $id): ?Event;
}
