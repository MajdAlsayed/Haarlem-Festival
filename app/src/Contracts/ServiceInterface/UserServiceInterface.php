<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

use App\Models\User;

interface UserServiceInterface
{
    public function getAllUsers(string $search = '', string $sortBy = 'created_at', string $sortDir = 'DESC'): array;

    public function findById(int $id): ?User;

    public function updateUser(int $id, int $roleId, string $firstName, string $lastName, string $email, bool $isActive): void;

    public function deleteUser(int $id): void;

    public function getAllRoles(): array;
}