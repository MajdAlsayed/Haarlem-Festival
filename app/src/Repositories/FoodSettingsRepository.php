<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class FoodSettingsRepository
{
    private static ?array $cache = null;

    public function getAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $db = Database::getConnection();
        $stmt = $db->query('SELECT setting_key, setting_value FROM food_settings');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $row) {
            $decoded = json_decode($row['setting_value'], true);
            $out[$row['setting_key']] = is_array($decoded) ? $decoded : $row['setting_value'];
        }

        self::$cache = $out;
        return $out;
    }
}