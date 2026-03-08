<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/** dance_settings table: hero_image, featured_images, day images/genres. JSON values decoded. */
class DanceSettingsRepository
{
    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    /** @return array<string, mixed> */
    public function getAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $db = Database::getConnection();
        $stmt = $db->query('SELECT setting_key, setting_value FROM dance_settings');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $row) {
            $key = $row['setting_key'];
            $val = $row['setting_value'];
            if ($val !== null) {
                $decoded = json_decode($val, true);
                $out[$key] = (is_array($decoded) || is_object($decoded)) ? $decoded : $val;
            } else {
                $out[$key] = $val;
            }
        }
        // no rows in DB = use config file
        if ($rows === []) {
            $fallback = require __DIR__ . '/../Config/dance.php';
            self::$cache = $fallback;
            return $fallback;
        }
        self::$cache = $out;
        return $out;
    }
}
