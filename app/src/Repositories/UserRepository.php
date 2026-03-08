<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use PDO;

final class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function existsEmail(string $email): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT 1 FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
        $stmt->execute(['email' => $email]);

        return (bool) $stmt->fetchColumn();
    }

    public function existsUsername(string $username): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT 1 FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);

        return (bool) $stmt->fetchColumn();
    }

    public function getRoleIdByName(string $name): ?int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT role_id FROM roles WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    public function createUser(
        int $roleId,
        string $username,
        string $email,
        string $passwordHash,
        string $firstName,
        string $lastName
    ): int {
        $db = Database::getConnection();
        $stmt = $db->prepare('
            INSERT INTO users (role_id, username, email, password_hash, first_name, last_name, is_active)
            VALUES (:role_id, :username, :email, :password_hash, :first_name, :last_name, 1)
        ');

        $stmt->execute([
            'role_id' => $roleId,
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);

        return (int) $db->lastInsertId();
    }

    public function updatePasswordHash(int $userId, string $passwordHash): void
    {
        $db = Database::getConnection();

        $stmt = $db->prepare('
            UPDATE users
            SET password_hash = :password_hash
            WHERE user_id = :user_id
        ');

        $stmt->execute([
            'password_hash' => $passwordHash,
            'user_id' => $userId,
        ]);
    }

    private function mapRowToUser(array $row): User
    {
        $user = new User();
        $user->id = (int) $row['user_id'];
        $user->roleId = (int) $row['role_id'];
        $user->username = (string) $row['username'];
        $user->email = (string) $row['email'];
        $user->passwordHash = (string) $row['password_hash'];
        $user->firstName = (string) $row['first_name'];
        $user->lastName = (string) $row['last_name'];
        $user->isActive = (bool) $row['is_active'];
        $user->createdAt = (string) $row['created_at'];

        return $user;
    }
}