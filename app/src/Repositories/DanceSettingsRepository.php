<?php

namespace App\Repositories;

use App\Core\Database;

/**
 * Reads dance_settings from the database (hero_image, featured_images, genres, etc.).
 * JSON values are decoded. If the database fails or has no rows, we use the config file.
 */
class DanceSettingsRepository
{
    public function getAll(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->query('SELECT setting_key, setting_value FROM dance_settings');
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return require __DIR__ . '/../Config/dance.php';
        }

        if (empty($rows)) {
            return require __DIR__ . '/../Config/dance.php';
        }

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
        return $out;
    }
}
