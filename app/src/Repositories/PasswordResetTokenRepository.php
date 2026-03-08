<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class PasswordResetTokenRepository
{
    public function create(int $userId, string $tokenHash, string $expiresAt): void
    {
        $db = Database::getConnection();

        $stmt = $db->prepare('
            INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
            VALUES (:user_id, :token_hash, :expires_at)
        ');

        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    public function invalidateAllForUser(int $userId): void
    {
        $db = Database::getConnection();

        $stmt = $db->prepare('
            UPDATE password_reset_tokens
            SET used_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id
              AND used_at IS NULL
        ');

        $stmt->execute([
            'user_id' => $userId,
        ]);
    }

    public function findValidByTokenHash(string $tokenHash): ?array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare('
            SELECT 
                prt.reset_id,
                prt.user_id,
                prt.expires_at,
                u.email,
                u.is_active
            FROM password_reset_tokens prt
            INNER JOIN users u ON u.user_id = prt.user_id
            WHERE prt.token_hash = :token_hash
              AND prt.used_at IS NULL
              AND prt.expires_at > CURRENT_TIMESTAMP
            LIMIT 1
        ');

        $stmt->execute([
            'token_hash' => $tokenHash,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function markUsed(int $resetId): void
    {
        $db = Database::getConnection();

        $stmt = $db->prepare('
            UPDATE password_reset_tokens
            SET used_at = CURRENT_TIMESTAMP
            WHERE reset_id = :reset_id
              AND used_at IS NULL
        ');

        $stmt->execute([
            'reset_id' => $resetId,
        ]);
    }
}