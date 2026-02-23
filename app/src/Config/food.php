<?php

declare(strict_types=1);

/**
 * Food page: image filenames (in /public/images/food/),
 * filter categories, featured first card override, restaurants + locals reviews.
 */
return [

    // Header / hero
    'hero_image' => 'food-hero.jpg',

    // Top section (optional: if you use “featured” like dance page)
    'featured_images' => [
        'Cafe-de-Roemer.jpg',
        'Restaurant-ML.jpg',
        'Grand-Cafe-Brinkman.jpg',
    ],

    // Filter buttons (as shown in your screenshot)
    'filter_labels' => ['All', 'Dutch', 'French', 'Vegan', 'European', 'Seafood'],

    // If you want the first card to be hard-coded (same idea as dance featured_first_card)
    'featured_first_card' => [
        'title' => 'Café de Roemer',
        'cuisines' => ['Dutch', 'Fish & Seafood', 'European'],
        'price' => '€35.00',
        'kids_price' => 'Kids<12 €17.50',
        'seats' => 35,
        'first_session' => '18:00',
        'walk_to_patronaat' => '8 min',
        'description' => 'Cozy café vibes in the city center with a festival-ready menu and warm hospitality.',
    ],

    // Restaurants (cards)
    'restaurants' => [

        [
            'image' => 'Cafe-de-Roemer.jpg',
            'name' => 'Café de Roemer',
            'tags' => ['Dutch', 'Seafood', 'European'],
            'rating' => 4.0,
            'price' => '€35.00',
            'kids_price' => 'Kids<12 €17.50',
            'seats' => 35,
            'first_session' => '18:00',
            'walk_to_patronaat' => '8 min',
        ],

        [
            'image' => 'Restaurant-ML.jpg',
            'name' => 'Restaurant ML',
            'tags' => ['Dutch', 'Seafood', 'European'],
            'rating' => 4.0,
            'price' => '€45.00',
            'kids_price' => 'Kids<12 €22.50',
            'seats' => 60,
            'first_session' => '17:00',
            'walk_to_patronaat' => '11 min',
        ],

        [
            'image' => 'Grand-Cafe-Brinkman.jpg',
            'name' => 'Grand Cafe Brinkman',
            'tags' => ['Dutch', 'European', 'Modern'],
            'rating' => 3.0,
            'price' => '€35.00',
            'kids_price' => 'Kids<12 €17.50',
            'seats' => 100,
            'first_session' => '16:30',
            'walk_to_patronaat' => '9 min',
        ],

        [
            'image' => 'Toujours.jpg',
            'name' => 'Toujours',
            'tags' => ['Dutch', 'Seafood', 'European'],
            'rating' => 3.0,
            'price' => '€35.00',
            'kids_price' => 'Kids<12 €17.50',
            'seats' => 48,
            'first_session' => '17:30',
            'walk_to_patronaat' => '11 min',
        ],

        [
            'image' => 'Ratatouille.jpg',
            'name' => 'Ratatouille',
            'tags' => ['French', 'Seafood', 'European'],
            'rating' => 4.0,
            'price' => '€45.00',
            'kids_price' => 'Kids<12 €22.50',
            'seats' => 52,
            'first_session' => '17:00',
            'walk_to_patronaat' => '14 min',
        ],

        [
            'image' => 'New-Vegas.jpg',
            'name' => 'New Vegas',
            'tags' => ['Vegan'],
            'rating' => 3.0,
            'price' => '€35.00',
            'kids_price' => 'Kids<12 €17.50',
            'seats' => 36,
            'first_session' => '17:00',
            'walk_to_patronaat' => '8 min',
        ],

        [
            'image' => 'Restaurant-Fris.jpg',
            'name' => 'Restaurant Fris',
            'tags' => ['Dutch', 'French', 'European'],
            'rating' => 4.0,
            'price' => '€45.00',
            'kids_price' => 'Kids<12 €22.50',
            'seats' => 45,
            'first_session' => '17:30',
            'walk_to_patronaat' => '22 min',
        ],

    ],

    // Locals reviews section (cards)
    'locals_reviews' => [

        [
            'reviewer' => 'Klus rust',
            'restaurant' => 'Café de Roemer',
            'rating' => 4.0,
            'text' =>
                "A cozy café with a terrace on the Botermarkt. Smoking is permitted on the terrace and it’s especially nice when the sun is out.\n\n"
                . "Inside there’s a big space with tables for larger or smaller groups, plus a conservatory with a view of the Botermarkt.\n\n"
                . "Menu and drinks offer a wide selection — not Michelin-level, but excellent value for money.\n\n"
                . "Friendly staff. All in all, a nice place to relax 😊",
        ],

        [
            'reviewer' => 'Jo aisen',
            'restaurant' => 'Ratatouille',
            'rating' => 5.0,
            'text' =>
                "Ratatouille is an amazing restaurant that gave me a truly memorable Michelin dining experience.\n\n"
                . "I chose the 5-course menu (€130) and both starters and mains were excellent — beautifully presented and perfectly balanced.\n\n"
                . "The marinated herring was prepared in a unique, non-traditional way: fresh, full of delightful surprises.",
        ],

        [
            'reviewer' => 'Guilherme Jacobucci',
            'restaurant' => 'Toujours',
            'rating' => 5.0,
            'text' =>
                "From the moment you walk into Toujours, you’re greeted with an atmosphere that blends modern charm and cozy elegance.\n\n"
                . "It truly stands out — not only for its prime location, but for its vibrant energy and impeccable attention to detail.",
        ],

    ],

];