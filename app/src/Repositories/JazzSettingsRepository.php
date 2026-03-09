<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class JazzSettingsRepository
{
    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    /** @return array<string, mixed> */
    public function getAll(): array
    {
        if (self::$cache !== null) return self::$cache;

        try {
            $db = Database::getConnection();
            $stmt = $db->query('SELECT setting_key, setting_value FROM jazz_settings');
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            self::$cache = require __DIR__ . '/../Config/jazz.php';
            return self::$cache;
        }

        if ($rows === []) {
            self::$cache = require __DIR__ . '/../Config/jazz.php';
            return self::$cache;
        }

        $out = [];
        foreach ($rows as $row) {
            $key = (string)$row['setting_key'];
            $val = $row['setting_value'];
            if ($val !== null) {
                $decoded = json_decode($val, true);
                $out[$key] = (is_array($decoded) || is_object($decoded)) ? $decoded : $val;
            } else {
                $out[$key] = null;
            }
        }

        // normalize to match config keys if they are stored as raw strings
        if (!isset($out['artist_pages'])) {
            $fallback = require __DIR__ . '/../Config/jazz.php';
            $out['artist_pages'] = $fallback['artist_pages'];
        }

        self::$cache = $out;
        return $out;
    }
}