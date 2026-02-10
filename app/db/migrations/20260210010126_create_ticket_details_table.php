<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTicketDetailsTable extends AbstractMigration
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
        $table = $this->table('ticket_details', ['id' => false, 'primary_key' => 'ticket_details_id']);

        $table->addColumn('ticket_details_id', 'integer', ['identity' => true])
            ->addColumn('event_id', 'integer', ['null' => true])
            ->addColumn('session_id', 'integer', ['null' => true])
            ->addColumn('ticket_type', 'enum', ['values' => ['event_ticket', 'day_pass', 'all_access_pass'], 'null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addForeignKey('event_id', 'events', 'event_id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('session_id', 'sessions', 'session_id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
