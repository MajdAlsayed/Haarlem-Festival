<?php

namespace App\Contracts;

use App\Models\Event;

/** Contract for event repo — use this in type hints so we can swap or mock. */
interface EventRepositoryInterface
{
    /** @return Event[] */
    public function getAll(): array;

    /** @return Event[] */
    public function getByCategory(string $eventTypeName): array;

    /** @return Event[] */
    public function getByCategoryAndDay(string $eventTypeName, string $eventDay): array;

    /** One event or null if not found */
    public function getById(int $id): ?Event;
}
