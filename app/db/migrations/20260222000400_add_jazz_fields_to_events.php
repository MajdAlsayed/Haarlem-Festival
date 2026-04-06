<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddJazzFieldsToEvents extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('events');

        // These fields are used only by Jazz cards/pages. Other categories can leave them NULL.
        $table
            ->addColumn('hall', 'string', ['limit' => 80, 'null' => true, 'after' => 'description'])
            ->addColumn('end_time', 'string', ['limit' => 10, 'null' => true, 'after' => 'start_time'])
            ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'after' => 'end_time'])

            // Prevent accidental duplicates for the same slot
            ->addIndex(['event_type_id', 'title', 'event_day', 'start_time'], [
                'unique' => true,
                'name' => 'uniq_event_slot'
            ])
            ->update();
    }
}