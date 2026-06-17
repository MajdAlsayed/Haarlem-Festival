<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Event;

interface EventRepositoryInterface
{
    public function getAll(): array;

    public function getByCategory(string $eventTypeName): array;

    public function getByCategoryAndDay(string $eventTypeName, string $eventDay): array;

    public function getById(int $id): ?Event;
}
