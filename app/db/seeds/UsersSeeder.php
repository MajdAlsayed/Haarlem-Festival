<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class UsersSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->execute("DELETE FROM users WHERE role_id = (SELECT role_id FROM roles WHERE name = 'customer')");

        $customerRoleId = $this->fetchRow("SELECT role_id FROM roles WHERE name = 'customer'")['role_id'];

        $users = [
            [
                'role_id'       => $customerRoleId,
                'username'      => 'john_doe',
                'email'         => 'john.doe@example.com',
                'password_hash' => password_hash('Password1!', PASSWORD_BCRYPT),
                'first_name'    => 'John',
                'last_name'     => 'Doe',
                'is_active'     => 1,
                'created_at'    => '2026-01-15 09:00:00',
            ],
            [
                'role_id'       => $customerRoleId,
                'username'      => 'emma_nl',
                'email'         => 'emma.jansen@example.nl',
                'password_hash' => password_hash('Password1!', PASSWORD_BCRYPT),
                'first_name'    => 'Emma',
                'last_name'     => 'Jansen',
                'is_active'     => 1,
                'created_at'    => '2026-01-20 11:30:00',
            ],
            [
                'role_id'       => $customerRoleId,
                'username'      => 'lucas_de_vries',
                'email'         => 'lucas.devries@example.nl',
                'password_hash' => password_hash('Password1!', PASSWORD_BCRYPT),
                'first_name'    => 'Lucas',
                'last_name'     => 'de Vries',
                'is_active'     => 1,
                'created_at'    => '2026-02-01 14:00:00',
            ],
            [
                'role_id'       => $customerRoleId,
                'username'      => 'sophie_b',
                'email'         => 'sophie.bakker@example.nl',
                'password_hash' => password_hash('Password1!', PASSWORD_BCRYPT),
                'first_name'    => 'Sophie',
                'last_name'     => 'Bakker',
                'is_active'     => 1,
                'created_at'    => '2026-02-10 08:45:00',
            ],
            [
                'role_id'       => $customerRoleId,
                'username'      => 'thomas_m',
                'email'         => 'thomas.muller@example.de',
                'password_hash' => password_hash('Password1!', PASSWORD_BCRYPT),
                'first_name'    => 'Thomas',
                'last_name'     => 'Muller',
                'is_active'     => 1,
                'created_at'    => '2026-02-14 16:20:00',
            ],
            [
                'role_id'       => $customerRoleId,
                'username'      => 'lisa_wang',
                'email'         => 'lisa.wang@example.com',
                'password_hash' => password_hash('Password1!', PASSWORD_BCRYPT),
                'first_name'    => 'Lisa',
                'last_name'     => 'Wang',
                'is_active'     => 0,
                'created_at'    => '2026-02-20 12:00:00',
            ],
            [
                'role_id'       => $customerRoleId,
                'username'      => 'peter_smit',
                'email'         => 'peter.smit@example.nl',
                'password_hash' => password_hash('Password1!', PASSWORD_BCRYPT),
                'first_name'    => 'Peter',
                'last_name'     => 'Smit',
                'is_active'     => 1,
                'created_at'    => '2026-03-01 10:10:00',
            ],
            [
                'role_id'       => $customerRoleId,
                'username'      => 'anna_k',
                'email'         => 'anna.kowalski@example.pl',
                'password_hash' => password_hash('Password1!', PASSWORD_BCRYPT),
                'first_name'    => 'Anna',
                'last_name'     => 'Kowalski',
                'is_active'     => 1,
                'created_at'    => '2026-03-05 15:30:00',
            ],
            [
                'role_id'       => $customerRoleId,
                'username'      => 'david_jones',
                'email'         => 'david.jones@example.co.uk',
                'password_hash' => password_hash('Password1!', PASSWORD_BCRYPT),
                'first_name'    => 'David',
                'last_name'     => 'Jones',
                'is_active'     => 1,
                'created_at'    => '2026-03-10 09:00:00',
            ],
        ];
        foreach ($users as $user) {
            $this->table('users')->insert($user)->saveData();
        }
    }
}