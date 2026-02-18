<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDisplayFieldsToEventTypes extends AbstractMigration
{
    public function change(): void
    {
        $this->table('event_types')
            ->addColumn('card_image', 'string', ['limit' => 255, 'null' => true, 'after' => 'description'])
            ->addColumn('info_path', 'string', ['limit' => 255, 'null' => true, 'after' => 'card_image'])
            ->update();
    }
}
