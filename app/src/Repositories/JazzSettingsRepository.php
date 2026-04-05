<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class JazzSettingsRepository
{
    /** @var array<string, mixed>|null */
    private static ?array $mergedCache = null;

    public static function clearCache(): void
    {
        self::$mergedCache = null;
    }

    /**
     * Full jazz config: defaults from Config/jazz.php overlaid by jazz_settings rows (JSON values).
     *
     * @return array<string, mixed>
     */
    public function getMergedConfig(): array
    {
        if (self::$mergedCache !== null) {
            return self::$mergedCache;
        }

        /** @var array<string, mixed> $defaults */
        $defaults = require __DIR__ . '/../Config/jazz.php';

        try {
            $db = Database::getConnection();
            $stmt = $db->query('SELECT setting_key, setting_value FROM jazz_settings');
            /** @var array<int, array{setting_key:string, setting_value:?string}> $rows */
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            self::$mergedCache = $defaults;

            return self::$mergedCache;
        }

        $fromDb = [];
        foreach ($rows as $row) {
            $key = (string) $row['setting_key'];
            $val = $row['setting_value'];
            if ($val === null || $val === '') {
                continue;
            }
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $fromDb[$key] = $decoded;
            } else {
                $fromDb[$key] = $val;
            }
        }

        $merged = $defaults;
        foreach (array_keys($defaults) as $k) {
            if (array_key_exists($k, $fromDb)) {
                $merged[$k] = $fromDb[$k];
            }
        }

        self::$mergedCache = $merged;

        return self::$mergedCache;
    }

    /** @return array<string, mixed> */
    public function getAll(): array
    {
        return $this->getMergedConfig();
    }

    /**
     * @param array<string, mixed> $settings Whitelisted keys from jazz.php
     */
    public function saveMany(array $settings): void
    {
        /** @var array<string, mixed> $defaults */
        $defaults = require __DIR__ . '/../Config/jazz.php';
        $allowed = array_keys($defaults);

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO jazz_settings (setting_key, setting_value, updated_at)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()'
        );

        foreach ($settings as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue;
            }
            $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $stmt->execute([$key, $json]);
        }

        self::clearCache();
    }
}
