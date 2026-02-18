<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMenuItemsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('menu_items');
        $table->addColumn('path', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('label', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->create();
    }
}
