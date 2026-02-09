<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('users', ['id' => false, 'primary_key' => 'user_id']);

        $table->addColumn('user_id', 'integer', ['identity' => true])
            ->addColumn('role_id', 'integer', ['null' => true])
            ->addColumn('email', 'string', ['limit' => 255])
            ->addColumn('password_hash', 'string', ['limit' => 255])
            ->addColumn('first_name', 'string', ['limit' => 255])
            ->addColumn('last_name', 'string', ['limit' => 255])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->addIndex(['email'], ['unique' => true])
            ->addForeignKey('role_id', 'roles', 'role_id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
