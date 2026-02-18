<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class PageSeeder extends AbstractSeed
{
    public function run(): void
    {
        // INSERT IGNORE so running multiple times or after other seeders doesn't fail (slug is UNIQUE).
        $sql = "INSERT IGNORE INTO pages (slug, title, is_published) VALUES ('home', 'Haarlem Festival', 1)";
        $this->table('pages')->getAdapter()->execute($sql);
    }
}
