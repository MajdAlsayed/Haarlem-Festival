<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * CMS / site admin login (role_id = 1). Idempotent: updates password & role if email exists.
 *
 * Default credentials (document in README, change in production):
 *   Email:    admin@haarlem.test
 *   Password: Admin123!
 *
 * Run: docker compose run --rm php vendor/bin/phinx seed:run -s AdminUserSeeder
 */
final class AdminUserSeeder extends AbstractSeed
{
    private const EMAIL = 'admin@haarlem.test';
    private const USERNAME = 'haarlem_admin';
    private const PLAIN_PASSWORD = 'Admin123!';
    private const ADMIN_ROLE_ID = 1;

    public function run(): void
    {
        if (!$this->hasTable('users') || !$this->hasTable('roles')) {
            echo "[SKIP] AdminUserSeeder: users or roles table missing\n";

            return;
        }

        $hash = password_hash(self::PLAIN_PASSWORD, PASSWORD_DEFAULT);
        if ($hash === false) {
            echo "[FAIL] AdminUserSeeder: password_hash failed\n";

            return;
        }

        $pdo = $this->getAdapter()->getConnection();
        $email = self::EMAIL;
        $quotedHash = $pdo->quote($hash);

        $existing = $this->fetchRow(
            'SELECT user_id FROM users WHERE LOWER(email) = LOWER(' . $pdo->quote($email) . ') LIMIT 1'
        );

        if ($existing !== null && isset($existing['user_id'])) {
            $uid = (int) $existing['user_id'];
            $this->execute(
                "UPDATE users SET role_id = " . self::ADMIN_ROLE_ID . ",
                    password_hash = {$quotedHash},
                    is_active = 1
                 WHERE user_id = {$uid}"
            );
            echo "[DONE] AdminUserSeeder: updated admin user_id={$uid} (" . self::EMAIL . ")\n";

            return;
        }

        $this->execute(
            'DELETE FROM users WHERE username = ' . $pdo->quote(self::USERNAME)
        );

        $this->execute(
            'INSERT INTO users (role_id, username, email, password_hash, first_name, last_name, is_active)
             VALUES (
                ' . self::ADMIN_ROLE_ID . ',
                ' . $pdo->quote(self::USERNAME) . ',
                ' . $pdo->quote($email) . ',
                ' . $quotedHash . ',
                ' . $pdo->quote('Site') . ',
                ' . $pdo->quote('Admin') . ',
                1
             )'
        );

        echo '[DONE] AdminUserSeeder: created ' . self::EMAIL . ' (username: ' . self::USERNAME . ")\n";
    }
}
