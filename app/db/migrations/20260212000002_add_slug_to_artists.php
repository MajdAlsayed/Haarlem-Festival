<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSlugToArtists extends AbstractMigration
{
    public function change(): void
    {
        // If the artists table does not exist yet (fresh database), create it here
        // with the expected columns including slug. This avoids errors when the
        // migration order runs this file before the dedicated create table migration.
        if (!$this->hasTable('artists')) {
            $this->table('artists')
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('bio', 'text', ['null' => true])
                ->addColumn('image_filename', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('sort_order', 'integer', ['default' => 0])
                ->addColumn('slug', 'string', ['limit' => 100, 'null' => true, 'after' => 'name'])
                ->addIndex(['slug'], ['unique' => true])
                ->create();
            return;
        }

        $table = $this->table('artists');
        if (!$table->hasColumn('slug')) {
            $table
                ->addColumn('slug', 'string', ['limit' => 100, 'null' => true, 'after' => 'name'])
                ->addIndex(['slug'], ['unique' => true])
                ->update();
        }
    }
}
