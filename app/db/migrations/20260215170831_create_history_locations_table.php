<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateHistoryLocationsTable extends AbstractMigration
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
        $table = $this->table('history_locations', ['id' => false, 'primary_key' => 'history_location_id']);

        $table
            ->addColumn('history_location_id', 'integer', ['identity' => true])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('slug', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('page_id', 'integer', ['null' => true])
            ->addColumn('sort_order', 'integer', ['null' => false, 'default' => 0])
            ->addIndex(['slug'], ['unique' => true])
            ->addForeignKey('page_id', 'pages', 'page_id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->create();
    }
}
