<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEventArtistsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('event_artists', [
            'id' => false,
            'primary_key' => ['event_id', 'artist_id'],
        ]);

        $table
            ->addColumn('event_id', 'integer')
            ->addColumn('artist_id', 'integer')
            ->addIndex(['event_id'])
            ->addIndex(['artist_id'])
            ->create();

        // Add FKs (if your DB already has these tables)
        $this->execute('ALTER TABLE event_artists 
            ADD CONSTRAINT fk_event_artists_event 
            FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE');

        $this->execute('ALTER TABLE event_artists 
            ADD CONSTRAINT fk_event_artists_artist 
            FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE');
    }
}