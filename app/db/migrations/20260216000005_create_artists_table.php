<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateArtistsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('artists');
        $table->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('bio', 'text', ['null' => true])
            ->addColumn('image_filename', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->create();
    }
}
