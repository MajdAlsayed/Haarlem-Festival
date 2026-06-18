<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTemplateToStories extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('stories');

        if (!$table->hasColumn('template')) {
            $table->addColumn('template', 'string', [
                'limit'   => 50,
                'default' => 'generic',
                'null'    => false,
                'after'   => 'language',
            ])->update();
        }
    }
}