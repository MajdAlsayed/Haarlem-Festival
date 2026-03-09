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
        // Some MariaDB setups can fail foreign key creation if the referenced
        // table was created with a different engine or collation. To keep this
        // migration working on all environments, we create the table first and
        // only add the foreign key when it is safe to do so.
        $table = $this->table('artist_photos');
        $table->addColumn('artist_id', 'integer', ['null' => false])
            ->addColumn('filename', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->create();

        // If the artists table exists and has an id column, add the FK.
        if ($this->hasTable('artists')) {
            $artists = $this->table('artists');
            if ($artists->hasColumn('id')) {
                $this->table('artist_photos')
                    ->addForeignKey('artist_id', 'artists', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                    ->update();
            }
        }
    }
}
