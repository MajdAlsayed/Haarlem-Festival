<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class FoodSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->table('food_settings')->getAdapter()->execute('DELETE FROM food_settings');
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



            // Restaurant cards (adjusted to match the config fields you showed in the screenshot)
           
            

            ['setting_key' => 'festival_dates', 'setting_value' => json_encode([
                ['value' => '07-28', 'label' => 'Thursday 28th July'],
                ['value' => '07-29', 'label' => 'Friday 29th July'],
                ['value' => '07-30', 'label' => 'Saturday 30th July'],
                ['value' => '07-31', 'label' => 'Sunday 31st July'],
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