<?php

declare(strict_types=1);

/**
 * Homepage event cards: event type name (from event_types.name) => image filename in /public/images/
 */
return [
    'card_images' => [
        'dance' => 'music.jpg',
        'jazz' => 'jazz.jpg',
        'history' => 'history.png',
        'yammy' => 'food.jpg',
        'stories' => 'stories.jpg',
    ],
    /**
     * INFO button URL per event type. Key = event type name (lowercase). Value = path or #.
     */
    'info_paths' => [
        'dance' => '/dance',
        'jazz' => '#',
        'history' => '#',
        'yammy' => '#',
        'stories' => '#',
    ],
];
