<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEmployeeRole extends AbstractMigration
{
    public function up(): void
    {
        $exists = $this->fetchRow("SELECT role_id FROM roles WHERE name = 'employee' LIMIT 1");
        if ($exists !== null) {
            return;
        }

        $this->table('roles')->insert([['name' => 'employee']])->saveData();
    }

    public function down(): void
    {
        $row = $this->fetchRow("SELECT role_id FROM roles WHERE name = 'employee' LIMIT 1");
        if ($row === null) {
            return;
        }
        $rid = (int) $row['role_id'];
        $customer = $this->fetchRow("SELECT role_id FROM roles WHERE name = 'customer' LIMIT 1");
        if ($customer !== null && isset($customer['role_id'])) {
            $cr = (int) $customer['role_id'];
            $this->execute("UPDATE users SET role_id = {$cr} WHERE role_id = {$rid}");
        }
        $this->execute("DELETE FROM roles WHERE name = 'employee'");
    }
}
