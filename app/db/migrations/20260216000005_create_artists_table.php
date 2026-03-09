<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateArtistsTable extends AbstractMigration
{
    public function change(): void
    {
        // If the artists table was already created in a previous migration
        // (for example, AddSlugToArtists creating it for fresh databases),
        // do nothing to keep this migration idempotent.
        if ($this->hasTable('artists')) {
            return;
        }

        $this->table('artists')
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('bio', 'text', ['null' => true])
            ->addColumn('image_filename', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->create();
    }
}
