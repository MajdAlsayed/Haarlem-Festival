<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEventDayAndStartTimeToEvents extends AbstractMigration
{
    public function change(): void
    {
        $this->table('events')
            ->addColumn('event_day', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('start_time', 'string', ['limit' => 10, 'null' => true])
            ->update();
    }
}
