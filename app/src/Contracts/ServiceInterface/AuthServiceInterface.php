<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

use App\Models\User;

interface AuthServiceInterface
{
    public function hashPassword(string $password): string;

    public function verifyPassword(string $password, string $hash): bool;

    public function passwordStrongEnough(string $password): bool;

    public function attemptLogin(string $emailOrUsername, string $password): ?User;

    public function register(
        string $username,
        string $email,
        string $password,
        string $passwordConfirm,
        string $firstName,
        string $lastName
    ): array;

    public function createPasswordResetRequest(string $identifier): array;

    public function validatePasswordResetToken(string $token): bool;

    public function resetPassword(string $token, string $password, string $passwordConfirm): array;
}