<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateOrderItemsTable extends AbstractMigration
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
        $table = $this->table('order_items', ['id' => false, 'primary_key' => 'order_item_id']);

        $table->addColumn('order_item_id', 'integer', ['identity' => true])
            ->addColumn('order_id', 'integer', ['null' => false])
            ->addColumn('ticket_details_id', 'integer', ['null' => false])
            ->addColumn('quantity', 'integer', ['null' => false])
            ->addColumn('unit_price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('line_total', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addForeignKey('order_id', 'orders', 'order_id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();

        // Need to add FK ticket_details_id later
    }
}
