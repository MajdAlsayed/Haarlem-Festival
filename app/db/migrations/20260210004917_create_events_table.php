<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEventsTable extends AbstractMigration
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
        $table = $this->table('events', ['id' => false, 'primary_key' => 'event_id']);

        $table->addColumn('event_id', 'integer', ['identity' => true])
            ->addColumn('event_type_id', 'integer', ['null' => false])
            ->addColumn('venue_id', 'integer', ['null' => false])
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
            ->addForeignKey('event_type_id', 'event_types', 'event_type_id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('venue_id', 'venues', 'venue_id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();
    }
}
