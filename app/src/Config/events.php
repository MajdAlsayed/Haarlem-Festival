<?php
declare(strict_types=1);

/**
 * Homepage event cards: event type name (lowercase) => image filename in /public/images/
 * Info paths control the INFO button on homepage cards.
 */
return [
    'card_images' => [
        'dance' => 'music.jpg',
        'jazz' => 'jazz.jpg',
        'history' => 'history.png',
        'yammy' => 'food.jpg',
        'stories' => 'stories.jpg',
    ],

    'info_paths' => [
        'dance'   => '/dance',
        'jazz'    => '/jazz',    // INFO on homepage Jazz card goes to Jazz page
        'history' => '/history', // History has its own section
        'yammy'   => '#',
        'stories' => '#',
    ],
];