<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Karsu discography: cover art in public/images/jazz/, audio in public/audio/Jazz audio/
 */
final class KarsuDiscographySeeder extends AbstractSeed
{
    public function run(): void
    {
        if (!$this->hasTable('artist_discography')) {
            echo "[SKIP] artist_discography missing — run migrations first\n";

            return;
        }

        $this->execute("DELETE FROM artist_discography WHERE artist_slug = 'karsu'");

        $rows = [
            [
                'artist_slug' => 'karsu',
                'title' => 'Confession',
                'release_year' => 2009,
                'duration_seconds' => 210,
                'play_count' => 156_200,
                'image_file' => 'Karsu - Confession - Karsu .png',
                'audio_file' => 'Jazz audio/Karsu - Confession - Karsu.mp3',
                'sort_order' => 1,
            ],
            [
                'artist_slug' => 'karsu',
                'title' => 'Mistress',
                'release_year' => 2010,
                'duration_seconds' => 226,
                'play_count' => 222_363,
                'image_file' => 'Karsu - Mistress - Karsu.png',
                'audio_file' => 'Jazz audio/Karsu - Mistress - Karsu.mp3',
                'sort_order' => 2,
            ],
            [
                'artist_slug' => 'karsu',
                'title' => 'All For Me',
                'release_year' => 2012,
                'duration_seconds' => 200,
                'play_count' => 189_400,
                'image_file' => 'Karsu - All For Me.png',
                'audio_file' => 'Jazz audio/Karsu - All For Me - Karsu.mp3',
                'sort_order' => 3,
            ],
        ];

        $this->table('artist_discography')->insert($rows)->save();

        echo "[DONE] KarsuDiscographySeeder: inserted Karsu discography tracks\n";
    }
}
