<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAudienceToStories extends AbstractMigration
{
    public function change(): void
    {
        $this->table('stories')
            ->addColumn('audience', 'string', [
                'limit'   => 50,
                'default' => '',
                'null'    => false,
                'after'   => 'template',
                'comment' => 'Target audience: all-ages, kids, teens, adults, families'
            ])
            ->update();
    }
}
