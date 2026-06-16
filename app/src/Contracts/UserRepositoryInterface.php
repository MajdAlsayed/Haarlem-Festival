<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getAllUsers(string $search = '', string $sortBy = 'created_at', string $sortDir = 'DESC'): array;

    public function findById(int $id): ?User;

    public function updateUser(User $user): void;

    public function deleteUser(int $id): void;

    public function getAllRoles(): array;
}