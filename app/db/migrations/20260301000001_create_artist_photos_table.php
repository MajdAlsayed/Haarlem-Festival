<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateArtistPhotosTable extends AbstractMigration
{
    public function change(): void
    {
        if ($this->hasTable('artist_photos')) {
            return;
        }
        $table = $this->table('artist_photos');
        $table->addColumn('artist_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('filename', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->addForeignKey('artist_id', 'artists', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
