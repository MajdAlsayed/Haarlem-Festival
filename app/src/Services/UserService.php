<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\UserServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\Models\User;

final class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ){
    }

    public function getAllUsers(string $search = '', string $sortBy = 'created_at', string $sortDir = 'DESC'): array
    {
        return $this->userRepository->getAllUsers($search, $sortBy, $sortDir);
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function updateUser(int $id, int $roleId, string $firstName, string $lastName, string $email, bool $isActive): void
    {
        $this->userRepository->updateUser($id, $roleId, $firstName, $lastName, $email, $isActive);
    }

    public function deleteUser(int $id): void
    {
        $this->userRepository->deleteUser($id);
    }

    public function getAllRoles(): array
    {
        return $this->userRepository->getAllRoles();
    }
}