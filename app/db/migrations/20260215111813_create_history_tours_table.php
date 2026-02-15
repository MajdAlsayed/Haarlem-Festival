<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateHistoryToursTable extends AbstractMigration
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
        $table = $this->table('history_tours', ['id' => false, 'primary_key' => 'history_tour_id']);

        $table
            ->addColumn('history_tour_id', 'integer', ['identity' => true])
            ->addColumn('session_id', 'integer', ['null' => false])
            ->addColumn('language_id', 'integer', ['null' => false])
            ->addColumn('tickets_available', 'integer', ['null' => false, 'default' => 12])
            ->addForeignKey('session_id', 'sessions', 'session_id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('language_id', 'languages', 'language_id', ['delete' => 'RESTRICT', 'update' => 'NO_ACTION'])
            ->create();
    }
}
