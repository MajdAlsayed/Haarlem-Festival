<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSessionsTable extends AbstractMigration
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
        $table = $this->table('sessions', ['id' => false, 'primary_key' => 'session_id']);

        $table->addColumn('session_id', 'integer', ['identity' => true])
            ->addColumn('event_id', 'integer', ['null' => false])
            ->addColumn('tickets_available', 'integer', ['null' => false])
            ->addColumn('start_time', 'datetime', ['null' => false])
            ->addColumn('end_time', 'datetime', ['null' => false])
            ->addForeignKey('event_id', 'events', 'event_id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
