<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePageBlocksTable extends AbstractMigration
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
        $table = $this->table('page_blocks', ['id' => false, 'primary_key' => 'block_id']);

        $table->addColumn('block_id', 'integer', ['identity' => true])
            ->addColumn('page_id', 'integer', ['null' => false])
            ->addColumn('block_type', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('content_json', 'json', ['null' => true])
            ->addColumn('sort_order', 'integer', ['null' => false, 'default' => 0])
            ->addForeignKey('page_id', 'pages', 'page_id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
