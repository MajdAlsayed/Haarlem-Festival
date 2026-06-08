<?php

namespace App\Repositories;

use App\Core\Repository;
use PDO;
use PDOException;

class StoriesSettingsRepository extends Repository
{
    /**
     * Get all stories settings as key-value array
     * @return array Array of setting_key => setting_value pairs
     */
    public function getAll(): array
    {
        try {
            $result = $this->db->query("SELECT setting_key, setting_value FROM stories_settings");
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);

            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }

            return $settings;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get a single setting value
     * @param string $key The setting key
     * @param string|null $default Default value if not found
     * @return string|null The setting value or default
     */
    public function get(string $key, ?string $default = null): ?string
    {
        try {
            $result = $this->db->prepare("SELECT setting_value FROM stories_settings WHERE setting_key = :key LIMIT 1");
            $result->execute(['key' => $key]);
            $row = $result->fetch(PDO::FETCH_ASSOC);

            return $row ? $row['setting_value'] : $default;
        } catch (PDOException $e) {
            return $default;
        }
    }

    /**
     * Get decoded JSON setting
     * @param string $key The setting key
     * @param array $default Default value if not found
     * @return array The decoded JSON array or default
     */
    public function getJson(string $key, array $default = []): array
    {
        $value = $this->get($key);

        if (!$value) {
            return $default;
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $default;
    }
}
