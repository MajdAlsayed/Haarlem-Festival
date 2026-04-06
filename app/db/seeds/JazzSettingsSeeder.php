<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class JazzSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $artistPages = [
            'gumbo-kings' => [
                'title' => 'Gumbo Kings',
                'tagline' => 'The Groove of New Orleans',
                'hero_image' => 'hero-gumbo-kings.jpg',
            ],
            'karsu' => [
                'title' => 'Karsu',
                'tagline' => 'A symphony of Jazz and Turkish Soul',
                'hero_image' => 'hero-karsu.jpg',
            ],
            'gare-du-nord' => [
                'title' => 'Gare du Nord',
                'tagline' => 'Cinematic Soul from the Urban Lounge',
                'hero_image' => 'hero-gare-du-nord.jpg',
            ],
        ];

        $rows = [
            ['setting_key' => 'hero_image', 'setting_value' => json_encode('hero-jazz.jpg')],
            ['setting_key' => 'placeholder_card', 'setting_value' => json_encode('placeholder-card.jpg')],
            ['setting_key' => 'artist_pages', 'setting_value' => json_encode($artistPages)],
        ];

        foreach ($rows as $r) {
            $key = addslashes($r['setting_key']);
            $val = addslashes((string)$r['setting_value']);
            $this->execute("
                INSERT INTO jazz_settings (setting_key, setting_value, updated_at)
                VALUES ('$key', '$val', NOW())
                ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_at=NOW()
            ");
        }
    }
}