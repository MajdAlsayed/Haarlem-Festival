<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Per-artist discography tracks (cover image + audio under public/).
 */
final class CreateArtistDiscographyTable extends AbstractMigration
{
    public function change(): void
    {
        if ($this->hasTable('artist_discography')) {
            return;
        }

        $table = $this->table('artist_discography', ['id' => false, 'primary_key' => 'track_id']);
        $table
            ->addColumn('track_id', 'integer', ['identity' => true])
            ->addColumn('artist_slug', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('release_year', 'integer', ['null' => true])
            ->addColumn('duration_seconds', 'integer', ['null' => true])
            ->addColumn('play_count', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('image_file', 'string', ['limit' => 512, 'null' => false, 'comment' => 'Filename under public/images/jazz/'])
            ->addColumn('audio_file', 'string', ['limit' => 512, 'null' => false, 'comment' => 'Path under public/audio/, forward slashes'])
            ->addColumn('sort_order', 'integer', ['default' => 0, 'null' => false])
            ->addIndex(['artist_slug'], ['name' => 'idx_artist_discography_slug'])
            ->create();
    }
}
