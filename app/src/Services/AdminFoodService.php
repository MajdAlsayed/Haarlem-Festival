<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RestaurantRepositoryInterface;
use App\Contracts\ServiceInterface\AdminFoodServiceInterface;
use App\Models\Restaurant;
use App\Repositories\FoodSettingsRepository;
use App\Repositories\RestaurantRepository;
use App\Repositories\SettingsRepository;

/**
 * Business logic for the food admin CMS.
 *
 * Responsibilities:
 *  - Validate POST data for restaurants and settings
 *  - Delegate persistence to RestaurantRepository / FoodSettingsRepository
 *  - Build transient Restaurant models from POST data (for form re-renders)
 *  - Parse/serialize the special food_settings formats
 *    (festival_dates: value|label lines → JSON array of objects)
 *    (filter_labels: one label per line → JSON array of strings)
 *    (locals_reviews: raw JSON textarea → validated JSON)
 */
final class AdminFoodService implements AdminFoodServiceInterface
{
            private SettingsRepository $settingsRepo;
            private FoodSettingsRepository $foodSettingsRepo;
            private RestaurantRepositoryInterface $restaurantRepo;

    public function __construct() {
    $this->restaurantRepo   = new RestaurantRepository();
    $this->foodSettingsRepo = new FoodSettingsRepository();
    $this->settingsRepo     = new SettingsRepository();    }


    // =========================================================================
    // Restaurants
    // =========================================================================

    /** @return Restaurant[] */
    public function getAllRestaurants(): array
    {
        return $this->restaurantRepo->getAll();
    }

    public function getRestaurantById(int $id): ?Restaurant
    {
        return $this->restaurantRepo->getById($id);
    }

    /**
     * Validate restaurant POST fields.
     *
     * @param  array<string, mixed> $post
     * @return list<string>  Error messages (empty = valid)
     */
    public function validateRestaurantPost(array $post): array
    {
        $errors = [];

        $name    = trim((string) ($post['name']    ?? ''));
        $slug    = trim((string) ($post['slug']    ?? ''));
        $address = trim((string) ($post['address'] ?? ''));
        $type    = trim((string) ($post['type']    ?? ''));

        if ($name === '')    $errors[] = 'Name is required.';
        if ($slug === '')    $errors[] = 'Slug is required.';
        if ($address === '') $errors[] = 'Address is required.';
        if ($type === '')    $errors[] = 'Cuisine type is required.';

        if ($slug !== '' && !preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $errors[] = 'Slug may only contain lowercase letters, numbers, and hyphens.';
        }

        $sessions = (int) ($post['sessions'] ?? 0);
        if ($sessions < 1 || $sessions > 3) {
            $errors[] = 'Sessions must be between 1 and 3.';
        }

        $stars = (int) ($post['stars'] ?? -1);
        if ($stars < 0 || $stars > 5) {
            $errors[] = 'Stars must be between 0 and 5.';
        }

        $seats = (int) ($post['seats'] ?? 0);
        if ($seats < 1) $errors[] = 'Seats must be at least 1.';

        $priceAdult = (float) ($post['price_adult'] ?? -1);
        if ($priceAdult < 0) $errors[] = 'Adult price must be 0 or more.';

        $priceKid = (float) ($post['price_kid'] ?? -1);
        if ($priceKid < 0) $errors[] = 'Child price must be 0 or more.';

        $kidAgeMax = (int) ($post['kid_age_max'] ?? 0);
        if ($kidAgeMax < 1 || $kidAgeMax > 17) {
            $errors[] = 'Child age max must be between 1 and 17.';
        }

        $duration = (float) ($post['duration_hours'] ?? 0);
        if ($duration <= 0) $errors[] = 'Session duration must be greater than 0.';

        $first = trim((string) ($post['first_session'] ?? ''));
        if ($first === '') $errors[] = 'First session time is required.';

        return $errors;
    }

    /**
     * Build a transient Restaurant from POST data for form re-renders.
     * The object is NOT persisted.
     */
    public function buildRestaurantFromPost(array $post): Restaurant
    {
        return new Restaurant(
            restaurantId:            (int)   ($post['restaurant_id']          ?? 0),
            name:                    (string) ($post['name']                   ?? ''),
            slug:                    (string) ($post['slug']                   ?? ''),
            address:                 (string) ($post['address']                ?? ''),
            type:                    (string) ($post['type']                   ?? ''),
            image:                   trim((string) ($post['image']             ?? '')) ?: null,
            sessions:                (int)   ($post['sessions']                ?? 1),
            durationHours:           (float) ($post['duration_hours']          ?? 1.5),
            firstSession:            (string) ($post['first_session']          ?? ''),
            secondSession:           (string) ($post['second_session']         ?? ''),
            thirdSession:            (string) ($post['third_session']          ?? ''),
            stars:                   (int)   ($post['stars']                   ?? 3),
            seats:                   (int)   ($post['seats']                   ?? 0),
            priceAdult:              (float) ($post['price_adult']             ?? 0.0),
            priceKid:                (float) ($post['price_kid']               ?? 0.0),
            kidAgeMax:               (int)   ($post['kid_age_max']             ?? 12),
            walkMinutesToPatronaat:  trim((string) ($post['walk_minutes_to_patronaat'] ?? '')) !== ''
                                        ? (int) $post['walk_minutes_to_patronaat']
                                        : null,
        );
    }

    /**
     * Persist a new restaurant.
     *
     * @param array<string, mixed> $post Validated POST data
     */
    public function createRestaurant(array $post): int
    {
        return $this->restaurantRepo->create($this->postToRow($post));
    }

    /**
     * Persist changes to an existing restaurant.
     *
     * @param array<string, mixed> $post Validated POST data
     */
    public function updateRestaurant(int $id, array $post): void
    {
        $this->restaurantRepo->update($id, $this->postToRow($post));
    }

    public function deleteRestaurant(int $id): void
    {
        $this->restaurantRepo->delete($id);
    }

    /**
     * Convert validated POST to a plain array suitable for the repository.
     *
     * @param  array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function postToRow(array $post): array
    {
        $walk = trim((string) ($post['walk_minutes_to_patronaat'] ?? ''));
        return [
            'name'                      => trim((string) $post['name']),
            'slug'                      => trim((string) $post['slug']),
            'address'                   => trim((string) $post['address']),
            'type'                      => trim((string) $post['type']),
            'image'                     => trim((string) ($post['image'] ?? '')) ?: null,
            'sessions'                  => (int) $post['sessions'],
            'duration_hours'            => number_format((float) $post['duration_hours'], 1, '.', ''),
            'first_session'             => trim((string) $post['first_session']),
            'second_session'            => trim((string) ($post['second_session'] ?? '')) ?: '00:00:00',
            'third_session'             => trim((string) ($post['third_session']  ?? '')) ?: '00:00:00',
            'stars'                     => (int) $post['stars'],
            'seats'                     => (int) $post['seats'],
            'price_adult'               => number_format((float) $post['price_adult'], 2, '.', ''),
            'price_kid'                 => number_format((float) $post['price_kid'],   2, '.', ''),
            'kid_age_max'               => (int) $post['kid_age_max'],
            'walk_minutes_to_patronaat' => $walk !== '' ? (int) $walk : null,
        ];
    }

    // =========================================================================
    // Food settings
    // =========================================================================

    /** @return array<string, mixed> */
    public function getAllFoodSettings(): array
    {
        return $this->foodSettingsRepo->getAll();
    }

    /**
     * Parse and persist the settings form.
     *
     * @param  array<string, mixed> $post
     * @return list<string>  Validation errors (empty = success)
     */
    public function saveSettings(array $post): array
    {
        $errors = [];

        // ── hero image ───────────────────────────────────────────────────────
        $heroImage = trim((string) ($post['hero_image'] ?? ''));
        if ($heroImage === '') {
            $errors[] = 'Hero image filename is required.';
        } elseif ($this->unsafeFilename($heroImage)) {
            $errors[] = 'Hero image: filename only, no path separators or "..".';
        }

        // ── reservation fee ──────────────────────────────────────────────────
        $feeRaw = trim((string) ($post['reservation_fee_per_person'] ?? ''));
        $fee    = (float) $feeRaw;
        if ($feeRaw === '' || $fee < 0) {
            $errors[] = 'Reservation fee must be 0 or more.';
        }

        // ── festival dates ───────────────────────────────────────────────────
        $festivalDates = $this->parsePipeLines((string) ($post['festival_dates_lines'] ?? ''));
        // Each entry must have non-empty value and label
        foreach ($festivalDates as $entry) {
            if (empty($entry['value']) || empty($entry['label'])) {
                $errors[] = 'Festival dates: every line must be "value|Label" with both parts non-empty.';
                break;
            }
        }

        // ── filter labels ────────────────────────────────────────────────────
        $filterLabels = $this->parseSimpleLines((string) ($post['filter_labels_lines'] ?? ''));
        if ($filterLabels === []) {
            $errors[] = 'At least one filter label is required (e.g. "All").';
        }

        // ── locals reviews JSON ───────────────────────────────────────────────
        $reviewsRaw = trim((string) ($post['locals_reviews_json'] ?? ''));
        $reviewsParsed = null;
        if ($reviewsRaw !== '') {
            $reviewsParsed = json_decode($reviewsRaw, true);
            if (!is_array($reviewsParsed)) {
                $errors[] = 'Locals reviews: invalid JSON. Fix the syntax and try again.';
            }
        }

        if ($errors !== []) {
            return $errors;
        }

        // ── persist each key ─────────────────────────────────────────────────
        $this->foodSettingsRepo->upsert('hero_image',                 $heroImage);
        $this->foodSettingsRepo->upsert('intro_heading',              trim((string) ($post['intro_heading'] ?? '')));
        $this->foodSettingsRepo->upsert('intro_text',                 trim((string) ($post['intro_text']    ?? '')));
        $this->foodSettingsRepo->upsert('reservation_fee_per_person', (string) $fee);
        $this->foodSettingsRepo->upsert('festival_dates',             json_encode($festivalDates,  JSON_UNESCAPED_UNICODE));
        $this->foodSettingsRepo->upsert('filter_labels',              json_encode($filterLabels,   JSON_UNESCAPED_UNICODE));

        if ($reviewsParsed !== null) {
            $this->foodSettingsRepo->upsert('locals_reviews', json_encode($reviewsParsed, JSON_UNESCAPED_UNICODE));
        } elseif ($reviewsRaw === '') {
            $this->foodSettingsRepo->upsert('locals_reviews', json_encode([]));
        }

        return [];
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Parse textarea lines of the form "value|Label" into
     * [{value: ..., label: ...}, ...].
     *
     * @return list<array{value: string, label: string}>
     */
    private function parsePipeLines(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $out   = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (!str_contains($line, '|')) continue;
            [$value, $label] = array_map('trim', explode('|', $line, 2));
            $out[] = ['value' => $value, 'label' => $label];
        }
        return $out;
    }

    /**
     * Parse textarea of one item per line into a plain string array.
     *
     * @return list<string>
     */
    private function parseSimpleLines(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $out   = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') $out[] = $line;
        }
        return $out;
    }

    private function unsafeFilename(string $name): bool
    {
        return str_contains($name, '..') || str_contains($name, '/') || str_contains($name, '\\');
    }
}