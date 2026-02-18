<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class DanceSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            ['setting_key' => 'hero_image', 'setting_value' => 'Dance page front picture.png'],
            ['setting_key' => 'featured_images', 'setting_value' => json_encode(['Dance-page-1.png', 'Dance-page-2.png', 'Dance-page-3.png'])],
            ['setting_key' => 'friday_images', 'setting_value' => json_encode(['dance-page-friday-1.png', 'dance-page-friday-2.png', 'dance-page-friday-3.png', 'dance-page-friday-4.png', 'dance-page-friday-5.png'])],
            ['setting_key' => 'saturday_images', 'setting_value' => json_encode(['dance-page-satuday-1.png', 'dance-page-satuday-2.png', 'dance-page-satuday-3.png', 'dance-page-satuday-4.png'])],
            ['setting_key' => 'sunday_images', 'setting_value' => json_encode(['dance-page-sunday-1.png', 'dance-page-sunday-2.png', 'dance-page-sunday-3.png', 'dance-page-sunday-4.png'])],
            ['setting_key' => 'friday_genres', 'setting_value' => json_encode(['HOUSE', 'TRANCE', 'DANCE', 'ELECTRONIC', 'TECH HOUSE'])],
            ['setting_key' => 'saturday_genres', 'setting_value' => json_encode(['MIXED GENRES', 'HOUSE', 'TRANCE / ELECTRO', 'ELECTROHOUSE'])],
            ['setting_key' => 'sunday_genres', 'setting_value' => json_encode(['MIXED GENRES', 'TRANCE', 'ELECTRONIC', 'DANCE'])],
            ['setting_key' => 'featured_genre_labels', 'setting_value' => json_encode(['HOUSE', 'TRANCE', 'DANCE'])],
            ['setting_key' => 'featured_times', 'setting_value' => json_encode(['Saturday • 20:00', 'Sunday • 22:00', 'Sunday • 23:00'])],
            ['setting_key' => 'featured_first_card', 'setting_value' => json_encode([
                'title' => 'Hardwell, Armin van Buuren, Martin Garrix — Back2Back Session',
                'venue' => 'Lichtfabriek, Haarlem',
                'time' => 'Saturday • 20:00',
                'description' => 'A high-energy B2B performance blending house, electro, and festival anthems.',
            ])],
        ];
        $this->table('dance_settings')->insert($data)->saveData();
    }
}
