<?php

namespace App\Repositories;

use App\Core\Database;

/**
 * Reads site_settings from the database (css_version, home_path, footer, etc.).
 * If the database fails or has no rows, we use the config file.
 */
class SettingsRepository
{
    public function getAll(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->query('SELECT setting_key, setting_value FROM site_settings');
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return require __DIR__ . '/../Config/app.php';
        }

        if (empty($rows)) {
            return require __DIR__ . '/../Config/app.php';
        }

        $out = [];
        foreach ($rows as $row) {
            $key = $row['setting_key'];
            $val = $row['setting_value'];
            if ($val !== null && str_starts_with($key, 'footer_')) {
                $decoded = json_decode($val, true);
                $out[$key] = is_array($decoded) ? $decoded : [];
            } else {
                $out[$key] = $val;
            }
        }

        return [
            'site_name' => $out['site_name'] ?? 'Haarlem Festival',
            'home_path' => $out['home_path'] ?? '/',
            'logo_filename' => $out['logo_filename'] ?? 'Logo.png',
            'logo_src' => $out['logo_src'] ?? '/images/jazz/Logo.jpg',
            'icons_path' => $out['icons_path'] ?? '/images/icons/',
            'default_event_location' => $out['default_event_location'] ?? 'Haarlem — Netherlands',
            'default_venue_city' => $out['default_venue_city'] ?? 'Haarlem',
            'default_event_time' => $out['default_event_time'] ?? '22:00',
            'css_version' => $out['css_version'] ?? '18',
            'footer' => [
                'social_icons' => $out['footer_social_icons'] ?? [],
                'app_icons' => $out['footer_app_icons'] ?? [],
                'app_labels' => $out['footer_app_labels'] ?? [],
            ],
        ];
    }

    public function get(string $key): ?string
    {
        $all = $this->getAll();
        $val = $all[$key] ?? null;
        return is_string($val) ? $val : null;
    }
}
