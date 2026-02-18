<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddImageFieldsToHistoryImages extends AbstractMigration
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
        $table = $this->table('history_images');

        $table->addColumn('page_id', 'integer', ['null' => true, 'after' => 'history_location_id'])
            ->addColumn('event_id', 'integer', ['null' => true, 'after' => 'page_id'])
            ->addColumn('image_type', 'enum', ['values' => ['hero', 'primary', 'gallery'], 'null' => false, 'default' => 'primary'])
            ->addForeignKey('page_id', 'pages', 'page_id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('event_id', 'events', 'event_id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])

            ->update();
    }
}
