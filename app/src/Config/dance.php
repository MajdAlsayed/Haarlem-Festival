<?php

declare(strict_types=1);

/**
 * Dance page: image filenames (in /public/images/dance/), genres, featured first card override, artists.
 */
return [
    'hero_image' => 'Dance page front picture.png',
    'featured_images' => ['Dance-page-1.png', 'Dance-page-2.png', 'Dance-page-3.png'],
    'friday_images' => ['dance-page-friday-1.png', 'dance-page-friday-2.png', 'dance-page-friday-3.png', 'dance-page-friday-4.png', 'dance-page-friday-5.png'],
    'saturday_images' => ['dance-page-satuday-1.png', 'dance-page-satuday-2.png', 'dance-page-satuday-3.png', 'dance-page-satuday-4.png'],
    'sunday_images' => ['dance-page-sunday-1.png', 'dance-page-sunday-2.png', 'dance-page-sunday-3.png', 'dance-page-sunday-4.png'],

    'friday_genres' => ['HOUSE', 'TRANCE', 'DANCE', 'ELECTRONIC', 'TECH HOUSE'],
    'saturday_genres' => ['MIXED GENRES', 'HOUSE', 'TRANCE / ELECTRO', 'ELECTROHOUSE'],
    'sunday_genres' => ['MIXED GENRES', 'TRANCE', 'ELECTRONIC', 'DANCE'],

    'featured_genre_labels' => ['HOUSE', 'TRANCE', 'DANCE'],
    'featured_times' => ['Saturday • 20:00', 'Sunday • 22:00', 'Sunday • 23:00'],

    'featured_first_card' => [
        'title' => 'Hardwell, Armin van Buuren, Martin Garrix — Back2Back Session',
        'venue' => 'Lichtfabriek, Haarlem',
        'time' => 'Saturday • 20:00',
        'description' => 'A high-energy B2B performance blending house, electro, and festival anthems.',
    ],

    'artists' => [
        [
            'image' => 'Image (Robbert Hardwell).png',
            'name' => 'Robbert Hardwell',
            'bio' => 'A high-energy dance night featuring Hardwell\'s signature big-room sound, explosive drops, and immersive festival-style atmosphere.',
        ],
        [
            'image' => 'Image (Tiësto).png',
            'name' => 'Tiësto',
            'bio' => 'A signature Tiësto club night featuring his blend of trance, techno, and electronic energy inside Haarlem\'s Slachthuis.',
        ],
    ],
];
