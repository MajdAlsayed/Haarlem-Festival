<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEventArtistsTable extends AbstractMigration
{
    public function change(): void
    {
        if (!$this->hasTable('event_artists')) {
            $table = $this->table('event_artists', [
                'id' => false,
                'primary_key' => ['event_id', 'artist_id'],
            ]);

            $table
                ->addColumn('event_id', 'integer', ['signed' => false])
                ->addColumn('artist_id', 'integer', ['signed' => false])
                ->addIndex(['event_id'])
                ->addIndex(['artist_id'])
                ->create();
        }

        $adapter = $this->getAdapter();
        $conn = $adapter->getConnection();

        $fkEvent = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'event_artists' AND CONSTRAINT_NAME = 'fk_event_artists_event'")->fetch();
        if (!$fkEvent) {
            $this->execute('ALTER TABLE event_artists 
                ADD CONSTRAINT fk_event_artists_event 
                FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE');
        }

        $fkArtist = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'event_artists' AND CONSTRAINT_NAME = 'fk_event_artists_artist'")->fetch();
        if (!$fkArtist) {
            // Ensure artist_id matches artists.id type (often UNSIGNED)
            $this->execute('ALTER TABLE event_artists MODIFY artist_id INT UNSIGNED NOT NULL');
            $this->execute('ALTER TABLE event_artists 
                ADD CONSTRAINT fk_event_artists_artist 
                FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE');
        }
    }
}