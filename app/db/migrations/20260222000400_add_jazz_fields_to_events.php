<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddJazzFieldsToEvents extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('events');

        if (!$table->hasColumn('hall')) {
            $table->addColumn('hall', 'string', [
                'limit' => 80,
                'null' => true,
                'after' => 'description'
            ]);
        }

        if (!$table->hasColumn('end_time')) {
            $table->addColumn('end_time', 'string', [
                'limit' => 10,
                'null' => true,
                'after' => 'start_time'
            ]);
        }

        if (!$table->hasColumn('price')) {
            $table->addColumn('price', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'null' => true,
                'after' => 'end_time'
            ]);
        }

        if (!$table->hasIndex(['event_type_id', 'title', 'event_day', 'start_time'])) {
            $table->addIndex(
                ['event_type_id', 'title', 'event_day', 'start_time'],
                [
                    'unique' => true,
                    'name' => 'uniq_event_slot'
                ]
            );
        }

        $table->update();
    }
}