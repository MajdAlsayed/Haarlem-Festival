<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class DanceSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $adapter = $this->table('dance_settings')->getAdapter();
        $adapter->execute('DELETE FROM dance_settings');
        $data = [
            ['setting_key' => 'hero_image', 'setting_value' => 'Dance page front picture.png'],
            ['setting_key' => 'featured_images', 'setting_value' => json_encode(['Dance-page-1.png', 'Dance-page-2.png', 'Dance-page-3.png'])],
            ['setting_key' => 'friday_images', 'setting_value' => json_encode(['dance-page-friday-1.png', 'dance-page-friday-2.png', 'dance-page-friday-3.png', 'dance-page-friday-4.png', 'dance-page-friday-5.png'])],
            ['setting_key' => 'saturday_images', 'setting_value' => json_encode(['dance-page-satuday-1.png', 'dance-page-satuday-2.png', 'dance-page-satuday-3.png', 'dance-page-satuday-4.png'])],
            ['setting_key' => 'sunday_images', 'setting_value' => json_encode(['dance-page-sunday-1.png', 'dance-page-sunday-2.png', 'dance-page-sunday-3.png', 'dance-page-sunday-4.png'])],
            ['setting_key' => 'friday_genres', 'setting_value' => json_encode(['HOUSE', 'TRANCE', 'DANCE', 'TRANCE', 'ELECTRONIC'])],
            ['setting_key' => 'saturday_genres', 'setting_value' => json_encode(['MIXED GENRES', 'HOUSE', 'TRANCE / ELECTRO', 'ELECTROHOUSE'])],
            ['setting_key' => 'sunday_genres', 'setting_value' => json_encode(['MIXED GENRES', 'TRANCE', 'DANCE', 'ELECTRONIC'])],
            ['setting_key' => 'featured_genre_labels', 'setting_value' => json_encode(['HOUSE', 'TRANCE', 'DANCE'])],
        ];
        $this->table('dance_settings')->insert($data)->saveData();
    }
}
