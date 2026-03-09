<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSlugToArtists extends AbstractMigration
{
    public function change(): void
    {
        $this->table('artists')
            ->addColumn('slug', 'string', ['limit' => 100, 'null' => true, 'after' => 'name'])
            ->addIndex(['slug'], ['unique' => true])
            ->update();
    }
}
