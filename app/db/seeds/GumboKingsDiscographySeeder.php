<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Gumbo Kings discography (artist_slug gumbo-kings): two tracks aligned with event preview audio.
 * Cover art: filenames under public/images/jazz/. Audio: paths under public/audio/ (see GumboKingsEventAudioSeeder).
 *
 * Run (from repo root): docker compose run --rm php vendor/bin/phinx seed:run -s GumboKingsDiscographySeeder
 */
final class GumboKingsDiscographySeeder extends AbstractSeed
{
    public function run(): void
    {
        if (!$this->hasTable('artist_discography')) {
            echo "[SKIP] artist_discography missing — run migrations first\n";

            return;
        }

        $this->execute("DELETE FROM artist_discography WHERE artist_slug = 'gumbo-kings'");

        $rows = [
            [
                'artist_slug' => 'gumbo-kings',
                'title' => 'Iko Iko — Dr. John',
                'release_year' => null,
                'duration_seconds' => null,
                'play_count' => 0,
                'image_file' => 'Gumbo-king-cover-page-and-event.png',
                'audio_file' => 'Jazz audio/Gumbo king Iko Iko - Dr. John.mp3',
                'sort_order' => 1,
            ],
            [
                'artist_slug' => 'gumbo-kings',
                'title' => 'Big Chief — Dr. John',
                'release_year' => null,
                'duration_seconds' => null,
                'play_count' => 0,
                'image_file' => 'hero-gumbo-kings.jpg',
                'audio_file' => 'Jazz audio/Gumbo king Big Chief - Dr. John.mp3',
                'sort_order' => 2,
            ],
        ];

        $this->table('artist_discography')->insert($rows)->save();

        echo "[DONE] GumboKingsDiscographySeeder: inserted 2 tracks for gumbo-kings\n";
    }
}
