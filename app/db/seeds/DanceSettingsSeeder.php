<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class DanceSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $adapter = $this->table('dance_settings')->getAdapter();
        $adapter->execute('DELETE FROM dance_settings');
        $venueCoords = json_encode([
            'Caprera Openluchttheater' => [52.4112, 4.6062],
            'Jopenkerk' => [52.3813, 4.6368],
            'Lichtfabriek' => [52.3890, 4.6330],
            'Patronaat' => [52.3820, 4.6380],
            'XO the Club' => [52.3815, 4.6370],
            'Slachthuis' => [52.3825, 4.6350],
        ]);
        $dayLabels = json_encode([
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ]);
        $data = [
            ['setting_key' => 'breadcrumb_home_label', 'setting_value' => 'HOME'],
            ['setting_key' => 'breadcrumb_dance_label', 'setting_value' => 'DANCE'],
            ['setting_key' => 'event_detail_photos_context', 'setting_value' => 'dance_event_detail'],
            ['setting_key' => 'event_detail_hero_fallback', 'setting_value' => 'DetailsPage/hero.png'],
            ['setting_key' => 'event_detail_gallery_fallbacks', 'setting_value' => json_encode(['DetailsPage/2.png', 'DetailsPage/3.png', 'DetailsPage/4.png'])],
            ['setting_key' => 'event_detail_list_path', 'setting_value' => '/dance'],
            ['setting_key' => 'default_event_day', 'setting_value' => 'friday'],
            ['setting_key' => 'event_detail_venue_country', 'setting_value' => 'Netherlands'],
            ['setting_key' => 'default_map_coordinates', 'setting_value' => json_encode([52.3813, 4.6368])],
            ['setting_key' => 'venue_coordinates', 'setting_value' => $venueCoords],
            ['setting_key' => 'day_labels', 'setting_value' => $dayLabels],
            ['setting_key' => 'hero_image', 'setting_value' => 'Dance page front picture.png'],
            ['setting_key' => 'featured_images', 'setting_value' => json_encode(['Dance-page-1.png', 'Dance-page-2.png', 'Dance-page-3.png'])],
            ['setting_key' => 'friday_images', 'setting_value' => json_encode(['dance-page-friday-1.png', 'dance-page-friday-2.png', 'dance-page-friday-3.png', 'dance-page-friday-4.png', 'dance-page-friday-5.png'])],
            ['setting_key' => 'saturday_images', 'setting_value' => json_encode(['dance-page-satuday-1.png', 'dance-page-satuday-2.png', 'dance-page-satuday-3.png', 'dance-page-satuday-4.png'])],
            ['setting_key' => 'sunday_images', 'setting_value' => json_encode(['dance-page-sunday-1.png', 'dance-page-sunday-2.png', 'dance-page-sunday-3.png', 'dance-page-sunday-4.png'])],
            ['setting_key' => 'friday_genres', 'setting_value' => json_encode(['HOUSE', 'TRANCE', 'DANCE', 'TRANCE', 'ELECTRONIC'])],
            ['setting_key' => 'saturday_genres', 'setting_value' => json_encode(['MIXED GENRES', 'HOUSE', 'TRANCE / ELECTRO', 'ELECTROHOUSE'])],
            ['setting_key' => 'sunday_genres', 'setting_value' => json_encode(['MIXED GENRES', 'TRANCE', 'DANCE', 'ELECTRONIC'])],
            ['setting_key' => 'featured_genre_labels', 'setting_value' => json_encode(['HOUSE', 'TRANCE', 'DANCE'])],
            ['setting_key' => 'venue_order_friday', 'setting_value' => json_encode([4, 7, 5, 8, 9])],
            ['setting_key' => 'venue_order_saturday', 'setting_value' => json_encode([6, 5, 7])],
            ['setting_key' => 'venue_order_sunday', 'setting_value' => json_encode([6, 5, 8, 7])],
            ['setting_key' => 'dance_images_base_path', 'setting_value' => '/images/dance/'],
            ['setting_key' => 'artist_detail_photos_context_hero', 'setting_value' => 'dance_artist_hero'],
            ['setting_key' => 'artist_detail_photos_context_schedule', 'setting_value' => 'dance_artist_schedule'],
            ['setting_key' => 'artist_detail_photos_context_music', 'setting_value' => 'dance_artist_music'],
            ['setting_key' => 'artist_detail_hero_fallback', 'setting_value' => 'Artist/hardwell hero.png'],
            ['setting_key' => 'artist_detail_schedule_fallbacks', 'setting_value' => json_encode(['hardwell' => 'Artist/hardwell6.png', 'tiesto' => 'Artist/tiesto3.png', 'default' => 'Artist/hardwell6.png'])],
            ['setting_key' => 'artist_detail_music_profile_slots', 'setting_value' => json_encode(['tiesto' => 'profile_tiesto', 'default' => 'profile'])],
            ['setting_key' => 'artist_detail_music_album_slots', 'setting_value' => json_encode(['tiesto' => 'album_cover_tiesto', 'default' => 'album_cover'])],
            ['setting_key' => 'artist_detail_music_profile_fallback', 'setting_value' => 'Artist/hardwell1.png'],
            ['setting_key' => 'artist_detail_music_album_fallback', 'setting_value' => 'Artist/hardwell2.jpg'],
            ['setting_key' => 'artist_detail_default_location', 'setting_value' => 'Netherlands'],
            ['setting_key' => 'artist_detail_default_album_title', 'setting_value' => 'Featured'],
            ['setting_key' => 'artist_detail_default_album_sub', 'setting_value' => 'Album'],
            ['setting_key' => 'artist_detail_gallery_target_count', 'setting_value' => '4'],
            ['setting_key' => 'artist_detail_hero_tagline_max_chars', 'setting_value' => '160'],
            ['setting_key' => 'artist_detail_gallery_stats_fallback', 'setting_value' => json_encode([['num' => '—', 'label' => 'PHOTOS'], ['num' => '—', 'label' => 'SHOWS']])],
        ];
        $this->table('dance_settings')->insert($data)->saveData();
    }
}
