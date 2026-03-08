<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seeds site_photos with filenames for dance event detail, artist schedule, artist music.
 * Contexts use dance_ prefix (e.g. dance_event_detail) so they don't conflict with other sections.
 * Run after migrations. phinx seed:run -s PhotosSeeder
 */
class PhotosSeeder extends AbstractSeed
{
    public function run(): void
    {
        $adapter = $this->table('site_photos')->getAdapter();
        $adapter->execute('DELETE FROM site_photos');

        $rows = [
            // Dance event detail page: hero and gallery by variant (default / club Armin / clubnight Hardwell)
            ['context' => 'dance_event_detail', 'key' => 'hero_default', 'filename' => 'DetailsPage/hero.png', 'sort_order' => 0],
            ['context' => 'dance_event_detail', 'key' => 'hero_club', 'filename' => 'DetailsPage/clubhero.png', 'sort_order' => 1],
            ['context' => 'dance_event_detail', 'key' => 'hero_clubnight', 'filename' => 'DetailsPage/clubnighthero.png', 'sort_order' => 2],
            ['context' => 'dance_event_detail', 'key' => 'gallery_default_1', 'filename' => 'DetailsPage/2.png', 'sort_order' => 10],
            ['context' => 'dance_event_detail', 'key' => 'gallery_default_2', 'filename' => 'DetailsPage/3.png', 'sort_order' => 11],
            ['context' => 'dance_event_detail', 'key' => 'gallery_default_3', 'filename' => 'DetailsPage/4.png', 'sort_order' => 12],
            ['context' => 'dance_event_detail', 'key' => 'gallery_club_1', 'filename' => 'DetailsPage/club1.png', 'sort_order' => 20],
            ['context' => 'dance_event_detail', 'key' => 'gallery_club_2', 'filename' => 'DetailsPage/club2.png', 'sort_order' => 21],
            ['context' => 'dance_event_detail', 'key' => 'gallery_club_3', 'filename' => 'DetailsPage/club3.png', 'sort_order' => 22],
            ['context' => 'dance_event_detail', 'key' => 'gallery_clubnight_1', 'filename' => 'DetailsPage/clubnight1.png', 'sort_order' => 30],
            ['context' => 'dance_event_detail', 'key' => 'gallery_clubnight_2', 'filename' => 'DetailsPage/clubnight2.png', 'sort_order' => 31],
            ['context' => 'dance_event_detail', 'key' => 'gallery_clubnight_3', 'filename' => 'DetailsPage/clubnight3.png', 'sort_order' => 32],
            // Dance artist detail: hero (Hardwell = red stage-lights version; replace hardwell hero.png if you see blue)
            ['context' => 'dance_artist_hero', 'key' => 'hardwell', 'filename' => 'Artist/hardwell hero.png', 'sort_order' => 0],
            ['context' => 'dance_artist_hero', 'key' => 'tiesto', 'filename' => 'Artist/tiestohero.png', 'sort_order' => 1],
            // Dance artist detail: schedule section image per artist
            ['context' => 'dance_artist_schedule', 'key' => 'hardwell', 'filename' => 'Artist/hardwell6.png', 'sort_order' => 0],
            ['context' => 'dance_artist_schedule', 'key' => 'tiesto', 'filename' => 'Artist/tiesto3.png', 'sort_order' => 1],
            // Dance artist music section: Hardwell
            ['context' => 'dance_artist_music', 'key' => 'profile', 'filename' => 'Artist/hardwell1.png', 'sort_order' => 0],
            ['context' => 'dance_artist_music', 'key' => 'album_cover', 'filename' => 'Artist/hardwell2.jpg', 'sort_order' => 1],
            ['context' => 'dance_artist_music', 'key' => 'track_1', 'filename' => 'Artist/hardwell3.jpg', 'sort_order' => 2],
            ['context' => 'dance_artist_music', 'key' => 'track_2', 'filename' => 'Artist/hardwell4.jpg', 'sort_order' => 3],
            // Dance artist music section: Tiësto (replace with your images when ready)
            ['context' => 'dance_artist_music', 'key' => 'profile_tiesto', 'filename' => 'Artist/tiesto1.png', 'sort_order' => 10],
            ['context' => 'dance_artist_music', 'key' => 'album_cover_tiesto', 'filename' => 'Artist/tiesto2.png', 'sort_order' => 11],
            ['context' => 'dance_artist_music', 'key' => 'track_1_tiesto', 'filename' => 'Artist/tiesto3.png', 'sort_order' => 12],
            ['context' => 'dance_artist_music', 'key' => 'track_2_tiesto', 'filename' => 'Artist/tiesto4.png', 'sort_order' => 13],
        ];

        $this->table('site_photos')->insert($rows)->saveData();
    }
}
