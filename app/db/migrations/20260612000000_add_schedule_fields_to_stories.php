<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddScheduleFieldsToStories extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('stories');

        if (!$table->hasColumn('event_day')) {
            $table->addColumn('event_day', 'string', [
                'limit' => 20,
                'null' => true,
                'after' => 'event_id',
            ]);
        }

        if (!$table->hasColumn('start_time')) {
            $table->addColumn('start_time', 'string', [
                'limit' => 10,
                'null' => true,
                'after' => 'event_day',
            ]);
        }

        if (!$table->hasColumn('end_time')) {
            $table->addColumn('end_time', 'string', [
                'limit' => 10,
                'null' => true,
                'after' => 'start_time',
            ]);
        }

        $table->update();

        $this->execute("
            UPDATE stories s
            LEFT JOIN events e ON e.event_id = s.event_id
            SET
                s.event_day = e.event_day,
                s.start_time = e.start_time,
                s.end_time = e.end_time
            WHERE s.event_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        $table = $this->table('stories');

        if ($table->hasColumn('end_time')) {
            $table->removeColumn('end_time');
        }

        if ($table->hasColumn('start_time')) {
            $table->removeColumn('start_time');
        }

        if ($table->hasColumn('event_day')) {
            $table->removeColumn('event_day');
        }

        $table->update();
    }
}
