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

        $sql = '
            SELECT
                restaurant_id,
                name,
                slug,
                address,
                type,
                image,
                sessions,
                duration_hours,
                first_session,
                stars,
                seats,
                price_adult,
                price_kid,
                kid_age_max,
                walk_minutes_to_patronaat
            FROM restaurants
            ORDER BY name ASC
        ';

        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'mapRow'], $rows);
    }

    public function getById(int $id): ?Restaurant
    {
        $db = Database::getConnection();

        $sql = '
            SELECT
                restaurant_id,
                name,
                slug,
                address,
                type,
                image,
                sessions,
                duration_hours,
                first_session,
                stars,
                seats,
                price_adult,
                price_kid,
                kid_age_max,
                walk_minutes_to_patronaat
            FROM restaurants
            WHERE restaurant_id = :id
        ';

        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRow($row) : null;
    }

    private function mapRow(array $row): Restaurant
    {
        return new Restaurant(
            restaurantId: (int) $row['restaurant_id'],
            name: (string) $row['name'],
            slug: (string) $row['slug'],
            address: (string) $row['address'],
            type: (string) $row['type'],
            image: $row['image'] ? (string) $row['image'] : null,
            sessions: (int) $row['sessions'],
            durationHours: (float) $row['duration_hours'],
            firstSession: (string) $row['first_session'],
            stars: (int) $row['stars'],
            seats: (int) $row['seats'],
            priceAdult: (float) $row['price_adult'],
            priceKid: (float) $row['price_kid'],
            kidAgeMax: (int) $row['kid_age_max'],
            walkMinutesToPatronaat: $row['walk_minutes_to_patronaat'] !== null
                ? (int) $row['walk_minutes_to_patronaat']
                : null
        );
    }
}