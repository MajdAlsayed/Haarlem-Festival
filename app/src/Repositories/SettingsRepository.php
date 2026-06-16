<?php

namespace App\Repositories;

use App\Core\Repository;

/**
 * Reads site_settings from the database (css_version, home_path, footer, etc.).
 * If the database fails or has no rows, we use the config file.
 */
class SettingsRepository extends Repository
{
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query('SELECT setting_key, setting_value FROM site_settings');
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
            'cms_home' => $this->mergeCmsHomeFromRaw($out),
        ];
    }

    /**
     * Homepage CMS: config defaults + site_settings overlay (keys cms_home_*).
     *
     * @return array<string, string>
     */
    public function getMergedCmsHome(): array
    {
        $app = require __DIR__ . '/../Config/app.php';
        $defaults = $app['cms_home'] ?? [];

        try {
            $stmt = $this->db->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'cms_home_%'");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return $defaults;
        }

        $prefix = 'cms_home_';
        foreach ($rows as $row) {
            $key = (string) $row['setting_key'];
            if (!str_starts_with($key, $prefix)) {
                continue;
            }
            $sub = substr($key, strlen($prefix));
            if (array_key_exists($sub, $defaults)) {
                $defaults[$sub] = (string) ($row['setting_value'] ?? '');
            }
        }

        return $defaults;
    }

    /**
     * @param array<string, mixed> $rawRows key => value from site_settings (partial)
     *
     * @return array<string, string>
     */
    private function mergeCmsHomeFromRaw(array $rawRows): array
    {
        $app = require __DIR__ . '/../Config/app.php';
        $defaults = $app['cms_home'] ?? [];
        $prefix = 'cms_home_';
        foreach ($rawRows as $key => $val) {
            if (!is_string($key) || !str_starts_with($key, $prefix)) {
                continue;
            }
            $sub = substr($key, strlen($prefix));
            if (array_key_exists($sub, $defaults) && is_string($val)) {
                $defaults[$sub] = $val;
            }
        }

        return $defaults;
    }

    public function upsertSetting(string $key, string $value): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );

            return $stmt->execute(['k' => $key, 'v' => $value]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function get(string $key): ?string
    {
        $all = $this->getAll();
        $val = $all[$key] ?? null;
        return is_string($val) ? $val : null;
    }
}
