<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EventsDescriptionText extends AbstractMigration
{
    public function change(): void
    {
        $this->table('events')
            ->changeColumn('description', 'text', ['null' => true])
            ->update();
    }
}
