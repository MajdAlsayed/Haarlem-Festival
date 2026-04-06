<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Reads and writes the food_settings key-value table.
 *
 * Values are stored as plain strings or JSON-encoded structures.
 * getAll() decodes JSON values automatically so callers receive
 * arrays/objects where appropriate.
 *
 * upsert() bypasses the in-process cache so that a settings-save
 * followed by a redirect-reload always shows fresh data.
 */
final class FoodSettingsRepository
{
    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    /** Force a fresh DB read (call after upsert()). */
    public static function clearCache(): void
    {
        self::$cache = null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $db   = Database::getConnection();
        $stmt = $db->query('SELECT setting_key, setting_value FROM food_settings');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $row) {
            $decoded = json_decode((string) $row['setting_value'], true);
            $out[$row['setting_key']] = is_array($decoded) ? $decoded : $row['setting_value'];
        }

        self::$cache = $out;
        return $out;
    }

    /**
     * Get a single setting value (raw string, not decoded).
     * Returns null when the key does not exist.
     */
    public function get(string $key): ?string
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT setting_value FROM food_settings WHERE setting_key = :k LIMIT 1'
        );
        $stmt->execute(['k' => $key]);
        $v = $stmt->fetchColumn();

        return $v !== false ? (string) $v : null;
    }

    /**
     * Insert or update a single setting.
     * Clears the in-process cache so subsequent getAll() re-reads from DB.
     */
    public function upsert(string $key, string $value): bool
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO food_settings (setting_key, setting_value)
             VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );

        $ok = $stmt->execute(['k' => $key, 'v' => $value]);
        self::clearCache();

        return $ok;
    }

    /**
     * Delete a setting row entirely.
     * Useful if a key should fall back to the hard-coded config default.
     */
    public function delete(string $key): void
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM food_settings WHERE setting_key = :k');
        $stmt->execute(['k' => $key]);
        self::clearCache();
    }
}