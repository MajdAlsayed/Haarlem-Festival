<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class SiteSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->table('site_settings')->getAdapter()->execute('DELETE FROM site_settings');
        $data = [
            ['setting_key' => 'site_name', 'setting_value' => 'Haarlem Festival'],
            ['setting_key' => 'home_path', 'setting_value' => '/'],
            ['setting_key' => 'logo_filename', 'setting_value' => 'Logo.png'],
            ['setting_key' => 'icons_path', 'setting_value' => '/images/icons/'],
            ['setting_key' => 'default_event_location', 'setting_value' => 'Haarlem — Netherlands'],
            ['setting_key' => 'default_venue_city', 'setting_value' => 'Haarlem'],
            ['setting_key' => 'default_event_time', 'setting_value' => '22:00'],
            ['setting_key' => 'css_version', 'setting_value' => '18'],
            ['setting_key' => 'tickets_intro', 'setting_value' => 'Explore all events from History tours to Jazz concerts, Dance nights, and Stories performances.'],
            ['setting_key' => 'footer_social_icons', 'setting_value' => json_encode(['insta icon.png', 'tiktok icon.png', 'facebook icon.png', 'youtube icon.png'])],
            ['setting_key' => 'footer_app_icons', 'setting_value' => json_encode(['apple.png', 'google play.png'])],
            ['setting_key' => 'footer_app_labels', 'setting_value' => json_encode(['App Store', 'Google Play'])],
        ];
        $this->table('site_settings')->insert($data)->saveData();
    }
}
