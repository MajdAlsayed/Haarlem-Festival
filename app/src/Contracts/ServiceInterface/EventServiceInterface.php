<?php

namespace App\Contracts\ServiceInterface;

use App\Models\Event;

interface EventServiceInterface
{

    public function getAll(): array;

    public function getByCategory(string $eventTypeName): array;

    public function getByCategoryAndDay(string $eventTypeName, string $eventDay): array;

    public function getById(int $id): ?Event;
}
