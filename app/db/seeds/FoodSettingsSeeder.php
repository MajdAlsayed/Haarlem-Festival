<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class FoodSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            // Header / intro (matches your page header + intro text)
            ['setting_key' => 'hero_image', 'setting_value' => 'food-hero.jpg'],
            ['setting_key' => 'intro_heading', 'setting_value' => 'Taste the Festival Spirit in Haarlem'],
            ['setting_key' => 'intro_text', 'setting_value' => 'Welcome to the heart of Haarlem\'s festival season! As the city comes alive with music, culture, and vibrant celebrations, our restaurants join in the spirit offering special menus, festive drinks, and warm hospitality. Whether you’re here to enjoy the performances or simply soak in the lively atmosphere, this is the perfect moment to explore Haarlem’s culinary scene. Bon appetit and happy festival!'],
            ['setting_key' => 'reservation_fee_per_person', 'setting_value' => 10],

            // Filters (same as config)
            ['setting_key' => 'filter_labels', 'setting_value' => json_encode(['All', 'Dutch', 'French', 'Vegan', 'European', 'Seafood'])],

            // Featured section (same idea as dance page: featured images + optional first card override)
            ['setting_key' => 'featured_images', 'setting_value' => json_encode([
                'Cafe-de-Roemer.jpg',
                'Restaurant-ML.jpg',
                'Grand-Cafe-Brinkman.jpg',
            ])],

            ['setting_key' => 'featured_first_card', 'setting_value' => json_encode([
                'title' => 'Café de Roemer',
                'cuisines' => ['Dutch', 'Fish & Seafood', 'European'],
                'price' => '€35.00',
                'kids_price' => 'Kids<12 €17.50',
                'seats' => 35,
                'first_session' => '18:00',
                'walk_to_patronaat' => '8 min',
                'description' => 'Cozy café vibes in the city center with a festival-ready menu and warm hospitality.',
            ])],

            // Restaurant cards (adjusted to match the config fields you showed in the screenshot)
            ['setting_key' => 'restaurants', 'setting_value' => json_encode([
                [
                    'name' => 'Café de Roemer',
                    'image' => 'Cafe-de-Roemer.jpg',
                    'tags' => ['Dutch', 'Fish & Seafood', 'European'],
                    'rating' => 4.0,
                    'price' => '€35.00',
                    'kids_price' => 'Kids<12 €17.50',
                    'seats' => 35,
                    'first_session' => '18:00',
                    'walk_to_patronaat' => '8 min',
                    'address' => 'Kleine Houtstraat 1, Haarlem',
                ],
                [
                    'name' => 'Restaurant ML',
                    'image' => 'Restaurant-ML.jpg',
                    'tags' => ['Dutch', 'Fish & Seafood', 'European'],
                    'rating' => 4.0,
                    'price' => '€45.00',
                    'kids_price' => 'Kids<12 €22.50',
                    'seats' => 60,
                    'first_session' => '17:00',
                    'walk_to_patronaat' => '11 min',
                    'address' => 'Kleine Houtstraat 70, Haarlem',
                ],
                [
                    'name' => 'Grand Cafe Brinkman',
                    'image' => 'Grand-Cafe-Brinkman.jpg',
                    'tags' => ['Dutch', 'European', 'Modern'],
                    'rating' => 3.0,
                    'price' => '€35.00',
                    'kids_price' => 'Kids<12 €17.50',
                    'seats' => 100,
                    'first_session' => '16:30',
                    'walk_to_patronaat' => '9 min',
                    'address' => 'Grote Markt 13, Haarlem',
                ],
                [
                    'name' => 'Toujours',
                    'image' => 'Toujours.jpg',
                    'tags' => ['Dutch', 'Fish & Seafood', 'European'],
                    'rating' => 3.0,
                    'price' => '€35.00',
                    'kids_price' => 'Kids<12 €17.50',
                    'seats' => 48,
                    'first_session' => '17:30',
                    'walk_to_patronaat' => '11 min',
                    'address' => 'Grote Markt 8, Haarlem',
                ],
                [
                    'name' => 'Ratatouille',
                    'image' => 'Ratatouille.jpg',
                    'tags' => ['French', 'Fish & Seafood', 'European'],
                    'rating' => 4.0,
                    'price' => '€45.00',
                    'kids_price' => 'Kids<12 €22.50',
                    'seats' => 52,
                    'first_session' => '17:00',
                    'walk_to_patronaat' => '14 min',
                    'address' => 'Lange Veerstraat 7, Haarlem',
                ],
                [
                    'name' => 'New Vegas',
                    'image' => 'New-Vegas.jpg',
                    'tags' => ['Vegan'],
                    'rating' => 3.0,
                    'price' => '€35.00',
                    'kids_price' => 'Kids<12 €17.50',
                    'seats' => 36,
                    'first_session' => '17:00',
                    'walk_to_patronaat' => '8 min',
                    'address' => 'Lange Veerstraat 10, Haarlem',
                ],
                [
                    'name' => 'Restaurant Fris',
                    'image' => 'Restaurant-Fris.jpg',
                    'tags' => ['Dutch', 'French', 'European'],
                    'rating' => 4.0,
                    'price' => '€45.00',
                    'kids_price' => 'Kids<12 €22.50',
                    'seats' => 45,
                    'first_session' => '17:30',
                    'walk_to_patronaat' => '22 min',
                    'address' => 'Korte Veerstraat 1, Haarlem',
                ],
            ])],

            // Locals reviews (matching the “locals reviews” cards)
            ['setting_key' => 'locals_reviews', 'setting_value' => json_encode([
                [
                    'reviewer' => 'Klus rust',
                    'restaurant' => 'Café de Roemer',
                    'rating' => 4.0,
                    'text' => "A cozy cafe with a terrace on the Botermarkt.\nSmoking is permitted on the terrace, a lovely spot, especially when the sun is shining.\n\nInside, there's a large space with several tables for large or small groups. There's also a conservatory with tables and a view of the Botermarkt.\n\nThe menu and drinks menu offer a wide selection; no Michelin-starred meals, but excellent value for money.\n\nFriendly staff.\nAll in all, a nice place to relax 😊",
                ],
                [
                    'reviewer' => 'Jo aisen',
                    'restaurant' => 'Ratatouille',
                    'rating' => 5.0,
                    'text' => "Ratatouille is an amazing restaurant that gave me a truly memorable Michelin dining experience.\n\nI chose the 5-course menu (€130), and both the starters and mains were excellent. The appetizers were beautifully presented — you couldn’t immediately tell what ingredients were used, yet every bite was perfectly balanced.\n\nThe marinated herring was also prepared in a unique, nontraditional way, fresh and full of delightful surprises.",
                ],
                [
                    'reviewer' => 'Guilherme Jacobucci',
                    'restaurant' => 'Toujours',
                    'rating' => 5.0,
                    'text' => "From the moment you walk into Toujours, you’re greeted with an atmosphere that perfectly blends modern charm and cozy elegance.\n\nLocated in the heart of Haarlem, this gem truly stands out — not only for its prime location but for its vibrant energy and impeccable attention to detail.",
                ],
            ])],
        ];

        $this->table('food_settings')->insert($data)->saveData();
    }
}