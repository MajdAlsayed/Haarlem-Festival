<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTemplateToStories extends AbstractMigration
{
    public function change(): void
    {
        $this->table('stories')
            ->addColumn('template', 'string', [
                'limit'   => 50,
                'default' => 'generic',
                'null'    => false,
                'after'   => 'language',
            ])
            ->update();
    }
}