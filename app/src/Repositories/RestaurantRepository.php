<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\RestaurantRepositoryInterface;
use App\Core\Database;
use App\Models\Restaurant;
use PDO;

final class RestaurantRepository implements RestaurantRepositoryInterface
{
    /** @return Restaurant[] */
    public function getAll(): array
    {
        $db = Database::getConnection();

        $stmt = $db->query('
            SELECT
                restaurant_id, name, slug, address, type, image,
                sessions, duration_hours,
                first_session, second_session, third_session,
                stars, seats,
                price_adult, price_kid, kid_age_max,
                walk_minutes_to_patronaat
            FROM restaurants
            ORDER BY name ASC
        ');

        return array_map([$this, 'mapRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?Restaurant
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            SELECT
                restaurant_id, name, slug, address, type, image,
                sessions, duration_hours,
                first_session, second_session, third_session,
                stars, seats,
                price_adult, price_kid, kid_age_max,
                walk_minutes_to_patronaat
            FROM restaurants
            WHERE restaurant_id = :id
            LIMIT 1
        ');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRow($row) : null;
    }

    // -------------------------------------------------------------------------
    // Write operations (used by AdminFoodService)
    // -------------------------------------------------------------------------

    /**
     * Insert a new restaurant row.
     *
     * @param  array<string, mixed> $data  Already-validated, snake_case keys
     * @return int  New restaurant_id
     */
    public function create(array $data): int
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            INSERT INTO restaurants (
                name, slug, address, type, image,
                sessions, duration_hours,
                first_session, second_session, third_session,
                stars, seats,
                price_adult, price_kid, kid_age_max,
                walk_minutes_to_patronaat
            ) VALUES (
                :name, :slug, :address, :type, :image,
                :sessions, :duration_hours,
                :first_session, :second_session, :third_session,
                :stars, :seats,
                :price_adult, :price_kid, :kid_age_max,
                :walk_minutes_to_patronaat
            )
        ');

        $stmt->execute($this->bindParams($data));

        return (int) $db->lastInsertId();
    }

    /**
     * Update an existing restaurant row.
     *
     * @param array<string, mixed> $data Already-validated, snake_case keys
     */
    public function update(int $id, array $data): void
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            UPDATE restaurants SET
                name                      = :name,
                slug                      = :slug,
                address                   = :address,
                type                      = :type,
                image                     = :image,
                sessions                  = :sessions,
                duration_hours            = :duration_hours,
                first_session             = :first_session,
                second_session            = :second_session,
                third_session             = :third_session,
                stars                     = :stars,
                seats                     = :seats,
                price_adult               = :price_adult,
                price_kid                 = :price_kid,
                kid_age_max               = :kid_age_max,
                walk_minutes_to_patronaat = :walk_minutes_to_patronaat,
                updated_at                = NOW()
            WHERE restaurant_id = :restaurant_id
        ');

        $params = $this->bindParams($data);
        $params['restaurant_id'] = $id;
        $stmt->execute($params);
    }

    /**
     * Delete a restaurant.
     * Throws a PDOException if FK constraints prevent deletion.
     */
    public function delete(int $id): void
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM restaurants WHERE restaurant_id = :id');
        $stmt->execute(['id' => $id]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build the PDO parameter map shared by create() and update().
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function bindParams(array $data): array
    {
        $walk = $data['walk_minutes_to_patronaat'] ?? null;

        return [
            'name'                      => (string)  $data['name'],
            'slug'                      => (string)  $data['slug'],
            'address'                   => (string)  $data['address'],
            'type'                      => (string)  $data['type'],
            'image'                     => isset($data['image']) && $data['image'] !== ''
                                               ? (string) $data['image']
                                               : null,
            'sessions'                  => (int)     $data['sessions'],
            'duration_hours'            => (string)  $data['duration_hours'],
            'first_session'             => (string)  $data['first_session'],
            'second_session'            => (string)  ($data['second_session'] ?? '00:00:00'),
            'third_session'             => (string)  ($data['third_session']  ?? '00:00:00'),
            'stars'                     => (int)     $data['stars'],
            'seats'                     => (int)     $data['seats'],
            'price_adult'               => (string)  $data['price_adult'],
            'price_kid'                 => (string)  $data['price_kid'],
            'kid_age_max'               => (int)     $data['kid_age_max'],
            'walk_minutes_to_patronaat' => $walk !== null && $walk !== '' ? (int) $walk : null,
        ];
    }

    private function mapRow(array $row): Restaurant
    {
        return new Restaurant(
            restaurantId:           (int)    $row['restaurant_id'],
            name:                   (string) $row['name'],
            slug:                   (string) $row['slug'],
            address:                (string) $row['address'],
            type:                   (string) $row['type'],
            image:                  $row['image'] !== null ? (string) $row['image'] : null,
            sessions:               (int)    $row['sessions'],
            durationHours:          (float)  $row['duration_hours'],
            firstSession:           (string) $row['first_session'],
            secondSession:          (string) $row['second_session'],
            thirdSession:           (string) $row['third_session'],
            stars:                  (int)    $row['stars'],
            seats:                  (int)    $row['seats'],
            priceAdult:             (float)  $row['price_adult'],
            priceKid:               (float)  $row['price_kid'],
            kidAgeMax:              (int)    $row['kid_age_max'],
            walkMinutesToPatronaat: $row['walk_minutes_to_patronaat'] !== null
                                        ? (int) $row['walk_minutes_to_patronaat']
                                        : null,
        );
    }
}