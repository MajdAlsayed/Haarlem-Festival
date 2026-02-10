<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateVenuesTable extends AbstractMigration
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
        $table = $this->table('venues', ['id' => false, 'primary_key' => 'venue_id']);

        $table->addColumn('venue_id', 'integer', ['identity' => true])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('address', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('city', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('capacity', 'integer', ['null' => false])
            ->create();
    }
}
