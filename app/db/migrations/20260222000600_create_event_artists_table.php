<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEventArtistsTable extends AbstractMigration
{
    public function change(): void
    {
        if ($this->hasTable('event_artists')) {
            return;
        }

        // Create the pivot table without DB-level foreign keys to avoid
        // engine/collation/type mismatches causing errno 150 on fresh DBs.
        $this->table('event_artists', [
                'id' => false,
                'primary_key' => ['event_id', 'artist_id'],
            ])
            ->addColumn('event_id', 'integer', ['null' => false])
            ->addColumn('artist_id', 'integer', ['null' => false])
            ->addIndex(['event_id'])
            ->addIndex(['artist_id'])
            ->create();
    }
}