<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTicketsTable extends AbstractMigration
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
        $table = $this->table('tickets', ['id' => false, 'primary_key' => 'ticket_id']);

        $table->addColumn('ticket_id', 'integer', ['identity' => true])
            ->addColumn('order_item_id', 'integer', ['null' => false])
            ->addColumn('ticket_code', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('status', 'enum', ['values' => ['valid', 'scanned', 'cancelled'], 'null' => false])
            ->addColumn('issued_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['ticket_code'], ['unique' => true])
            ->addForeignKey('order_item_id', 'order_items', 'order_item_id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
