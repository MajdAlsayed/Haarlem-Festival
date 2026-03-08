<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateHistoryImagesTable extends AbstractMigration
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
        $table = $this->table('history_images', ['id' => false, 'primary_key' => 'history_image_id']);

        $table
            ->addColumn('history_image_id', 'integer', ['identity' => true])
            ->addColumn('history_location_id', 'integer', ['null' => false])
            ->addColumn('image_url', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('alt_text', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('is_primary', 'boolean', ['null' => false, 'default' => false])
            ->addColumn('sort_order', 'integer', ['null' => false, 'default' => 0])
            ->addForeignKey('history_location_id', 'history_locations', 'history_location_id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
