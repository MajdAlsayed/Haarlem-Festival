<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Door staff login (role employee). Scanner only — not full CMS.
 *
 *   Email:    employee@haarlem.test
 *   Password: Employee123!
 *
 * Run after migrations (employee role exists):
 *   docker compose run --rm php vendor/bin/phinx seed:run -s EmployeeUserSeeder
 */
final class EmployeeUserSeeder extends AbstractSeed
{
    private const EMAIL = 'employee@haarlem.test';
    private const USERNAME = 'haarlem_employee';
    private const PLAIN_PASSWORD = 'Employee123!';

    public function run(): void
    {
        if (!$this->hasTable('users') || !$this->hasTable('roles')) {
            echo "[SKIP] EmployeeUserSeeder: users or roles table missing\n";

            return;
        }

        $roleRow = $this->fetchRow("SELECT role_id FROM roles WHERE name = 'employee' LIMIT 1");
        if ($roleRow === null || !isset($roleRow['role_id'])) {
            $this->table('roles')->insert([['name' => 'employee']])->saveData();
            $roleRow = $this->fetchRow("SELECT role_id FROM roles WHERE name = 'employee' LIMIT 1");
        }
        if ($roleRow === null || !isset($roleRow['role_id'])) {
            echo "[FAIL] EmployeeUserSeeder: could not resolve employee role_id\n";

            return;
        }

        $employeeRoleId = (int) $roleRow['role_id'];

        $hash = password_hash(self::PLAIN_PASSWORD, PASSWORD_DEFAULT);
        if ($hash === false) {
            echo "[FAIL] EmployeeUserSeeder: password_hash failed\n";

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
                "UPDATE users SET role_id = {$employeeRoleId},
                    password_hash = {$quotedHash},
                    is_active = 1
                 WHERE user_id = {$uid}"
            );
            echo "[DONE] EmployeeUserSeeder: updated user_id={$uid} (" . self::EMAIL . ")\n";

            return;
        }

        $this->execute(
            'DELETE FROM users WHERE username = ' . $pdo->quote(self::USERNAME)
        );

        $this->execute(
            'INSERT INTO users (role_id, username, email, password_hash, first_name, last_name, is_active)
             VALUES (
                ' . $employeeRoleId . ',
                ' . $pdo->quote(self::USERNAME) . ',
                ' . $pdo->quote($email) . ',
                ' . $quotedHash . ',
                ' . $pdo->quote('Door') . ',
                ' . $pdo->quote('Staff') . ',
                1
             )'
        );

        echo '[DONE] EmployeeUserSeeder: created ' . self::EMAIL . ' (username: ' . self::USERNAME . ")\n";
    }
}
