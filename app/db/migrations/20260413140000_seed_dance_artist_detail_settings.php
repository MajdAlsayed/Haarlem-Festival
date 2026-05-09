<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** /dance/artist/{slug} defaults in dance_settings (merged with dance.php). */
final class SeedDanceArtistDetailSettings extends AbstractMigration
{
    public function up(): void
    {
        $schedule = [
            'hardwell' => 'Artist/hardwell6.png',
            'tiesto' => 'Artist/tiesto3.png',
            'default' => 'Artist/hardwell6.png',
        ];
        $profileSlots = ['tiesto' => 'profile_tiesto', 'default' => 'profile'];
        $albumSlots = ['tiesto' => 'album_cover_tiesto', 'default' => 'album_cover'];
        $galleryStats = [
            ['num' => '—', 'label' => 'PHOTOS'],
            ['num' => '—', 'label' => 'SHOWS'],
        ];

        $rows = [
            ['dance_images_base_path', '/images/dance/'],
            ['artist_detail_photos_context_hero', 'dance_artist_hero'],
            ['artist_detail_photos_context_schedule', 'dance_artist_schedule'],
            ['artist_detail_photos_context_music', 'dance_artist_music'],
            ['artist_detail_hero_fallback', 'Artist/hardwell hero.png'],
            ['artist_detail_schedule_fallbacks', json_encode($schedule)],
            ['artist_detail_music_profile_slots', json_encode($profileSlots)],
            ['artist_detail_music_album_slots', json_encode($albumSlots)],
            ['artist_detail_music_profile_fallback', 'Artist/hardwell1.png'],
            ['artist_detail_music_album_fallback', 'Artist/hardwell2.jpg'],
            ['artist_detail_default_location', 'Netherlands'],
            ['artist_detail_default_album_title', 'Featured'],
            ['artist_detail_default_album_sub', 'Album'],
            ['artist_detail_gallery_target_count', '4'],
            ['artist_detail_hero_tagline_max_chars', '160'],
            ['artist_detail_gallery_stats_fallback', json_encode($galleryStats)],
        ];

        $pdo = $this->getAdapter()->getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO dance_settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        foreach ($rows as [$key, $val]) {
            $stmt->execute(['k' => $key, 'v' => $val]);
        }
    }

    public function down(): void
    {
        $keys = [
            'dance_images_base_path',
            'artist_detail_photos_context_hero',
            'artist_detail_photos_context_schedule',
            'artist_detail_photos_context_music',
            'artist_detail_hero_fallback',
            'artist_detail_schedule_fallbacks',
            'artist_detail_music_profile_slots',
            'artist_detail_music_album_slots',
            'artist_detail_music_profile_fallback',
            'artist_detail_music_album_fallback',
            'artist_detail_default_location',
            'artist_detail_default_album_title',
            'artist_detail_default_album_sub',
            'artist_detail_gallery_target_count',
            'artist_detail_hero_tagline_max_chars',
            'artist_detail_gallery_stats_fallback',
        ];
        $pdo = $this->getAdapter()->getConnection();
        $in = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $pdo->prepare("DELETE FROM dance_settings WHERE setting_key IN ($in)");
        $stmt->execute($keys);
    }
}
