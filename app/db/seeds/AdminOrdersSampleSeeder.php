<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Demo admin user + sample orders for testing Export Orders (CMS).
 * Run: docker compose run --rm php vendor/bin/phinx seed:run -s AdminOrdersSampleSeeder
 *
 * Login: admin@haarlem.test / Admin123!
 */
final class AdminOrdersSampleSeeder extends AbstractSeed
{
    public function run(): void
    {
        $roles = $this->fetchAll("SELECT role_id FROM roles WHERE name = 'admin' LIMIT 1");
        if ($roles === [] || $roles === null) {
            throw new \RuntimeException('roles table must exist (run migrations).');
        }
        $adminRoleId = (int) $roles[0]['role_id'];

        $hash = password_hash('Admin123!', PASSWORD_DEFAULT);
        $existing = $this->fetchAll("SELECT user_id FROM users WHERE email = 'admin@haarlem.test' LIMIT 1");

        if ($existing === [] || $existing === null) {
            $this->table('users')->insert([
                [
                    'role_id' => $adminRoleId,
                    'username' => 'admin_demo',
                    'email' => 'admin@haarlem.test',
                    'password_hash' => $hash,
                    'first_name' => 'Admin',
                    'last_name' => 'Demo',
                    'is_active' => 1,
                ],
            ])->saveData();
        } else {
            $this->execute(
                'UPDATE users SET role_id = ' . $adminRoleId . ", password_hash = '" . $hash . "' WHERE email = 'admin@haarlem.test'"
            );
        }

        $adminRows = $this->fetchAll("SELECT user_id FROM users WHERE email = 'admin@haarlem.test' LIMIT 1");
        $adminUserId = (int) $adminRows[0]['user_id'];

        $countRows = $this->fetchAll('SELECT COUNT(*) AS c FROM orders');
        if ($countRows !== [] && (int) $countRows[0]['c'] > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->table('orders')->insert([
            [
                'user_id' => $adminUserId,
                'status' => 'paid',
                'total_amount' => 49.99,
                'created_at' => $now,
                'paid_at' => $now,
            ],
            [
                'user_id' => $adminUserId,
                'status' => 'pending',
                'total_amount' => 120.00,
                'created_at' => $now,
                'paid_at' => null,
            ],
            [
                'user_id' => null,
                'status' => 'paid',
                'total_amount' => 25.50,
                'created_at' => $now,
                'paid_at' => $now,
            ],
        ])->saveData();
    }
}
