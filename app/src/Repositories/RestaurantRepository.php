<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Restaurant;
use PDO;

final class RestaurantRepository
{
    /** @return Restaurant[] */
    public function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT * FROM restaurants ORDER BY name ASC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'mapRow'], $rows);
    }

    public function getById(int $id): ?Restaurant
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM restaurants WHERE restaurant_id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRow($row) : null;
    }

    private function mapRow(array $r): Restaurant
    {
        return new Restaurant(
            (int)$r['restaurant_id'],
            (string)$r['name'],
            (string)$r['slug'],
            (string)$r['address'],
            (string)$r['type'],
            $r['image'] !== null ? (string)$r['image'] : null,
            (int)$r['sessions'],
            (float)$r['duration_hours'],
            (string)$r['first_session'],
            (int)$r['stars'],
            (int)$r['seats'],
            (float)$r['price_adult'],
            (float)$r['price_kid'],
            (int)$r['kid_age_max'],
            $r['walk_minutes_to_patronaat'] !== null ? (int)$r['walk_minutes_to_patronaat'] : null
        );
    }
}