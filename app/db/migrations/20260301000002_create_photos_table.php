<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Stores photo filenames for all site contexts: dance page, event detail, artist schedule, etc.
 * Artist gallery remains in artist_photos; this table is for everything else.
 */
final class CreatePhotosTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('photos');
        $table->addColumn('context', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('key', 'string', ['limit' => 80, 'null' => true])
            ->addColumn('filename', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->addIndex(['context'])
            ->create();
    }
}
