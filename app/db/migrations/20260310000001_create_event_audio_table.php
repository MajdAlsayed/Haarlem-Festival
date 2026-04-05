<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Preview audio clips for events (e.g. Gumbo Kings page play buttons).
 */
final class CreateEventAudioTable extends AbstractMigration
{
    public function change(): void
    {
        if ($this->hasTable('event_audio')) {
            return;
        }

        $table = $this->table('event_audio', ['id' => false, 'primary_key' => 'audio_id']);
        $table
            ->addColumn('audio_id', 'integer', ['identity' => true])
            ->addColumn('event_id', 'integer', ['null' => false])
            ->addColumn('file_path', 'string', ['limit' => 512, 'null' => false, 'comment' => 'Path under public/audio/, use forward slashes'])
            ->addColumn('track_title', 'string', ['limit' => 255, 'null' => true])
            ->addIndex(['event_id'], ['unique' => true, 'name' => 'uq_event_audio_event_id'])
            ->addForeignKey('event_id', 'events', 'event_id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
