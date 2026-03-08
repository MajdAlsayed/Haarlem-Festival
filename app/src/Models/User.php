<?php

declare(strict_types=1);

namespace App\Models;

class User
{
    public int $id;
    public int $roleId;
    public string $username;
    public string $email;
    public string $passwordHash;
    public string $firstName;
    public string $lastName;
    public bool $isActive;
    public string $createdAt;
}