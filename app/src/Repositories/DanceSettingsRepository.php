<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Dance CMS key/value store: database overrides dance.php; falls back to the config file if the table is empty or unavailable.
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
            $out[$key] = $this->decodeSettingValue($val);
        }

        return $out;
    }

    /**
     * Config defaults + DB overrides (use for public Dance page and CMS form).
     *
     * @return array<string, mixed>
     */
    public function getMergedWithConfig(): array
    {
        $base = require __DIR__ . '/../Config/dance.php';

        try {
            $db = Database::getConnection();
            $stmt = $db->query('SELECT setting_key, setting_value FROM dance_settings');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return $base;
        }

        foreach ($rows as $row) {
            $key = $row['setting_key'];
            $base[$key] = $this->decodeSettingValue($row['setting_value']);
        }

        return $base;
    }

    public function upsertSetting(string $key, string $value): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO dance_settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );

        return $stmt->execute(['k' => $key, 'v' => $value]);
    }

    private function decodeSettingValue(?string $val): mixed
    {
        if ($val === null) {
            return null;
        }

        $decoded = json_decode($val, true);
        if (is_array($decoded) || is_object($decoded)) {
            return $decoded;
        }

        return $val;
    }
}
