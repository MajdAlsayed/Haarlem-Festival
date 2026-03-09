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

        // Use Phinx's foreign key helpers so types stay aligned with
        // events.event_id and artists.id, avoiding errno 150 on fresh DBs.
        $this->table('event_artists', [
                'id' => false,
                'primary_key' => ['event_id', 'artist_id'],
            ])
            ->addColumn('event_id', 'integer', ['null' => false])
            ->addColumn('artist_id', 'integer', ['null' => false])
            ->addIndex(['event_id'])
            ->addIndex(['artist_id'])
            ->addForeignKey('event_id', 'events', 'event_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('artist_id', 'artists', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}