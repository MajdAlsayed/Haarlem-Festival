<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Restaurant;

interface RestaurantRepositoryInterface
{
    /** @return Restaurant[] */
    public function getAll(): array;

    public function getById(int $id): ?Restaurant;

    /** @param array<string, mixed> $data */
    public function create(array $data): int;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void;

    public function delete(int $id): void;
}