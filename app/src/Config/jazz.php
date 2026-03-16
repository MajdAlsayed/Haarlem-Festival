<?php
declare(strict_types=1);

return [
    'hero_image' => 'hero-jazz.jpg',
    'placeholder_card' => 'placeholder-card.jpg',

    /**
     * Event title (from DB) => image filename in /images/jazz/
     * Matches prototype order: row 1–4 card images for filtering system.
     */
    /**
     * Order of event titles for "All Events" view (row 1 left→right, row 2, …).
     * Ensures grid matches prototype: 6+6+6+1 cards.
     */
    'all_events_order' => [
        'Gumbo Kings', 'Evolve', 'Ntjam Rosie', 'Wicked Jazz Sounds', 'Wouter Hamel', 'Jonna Frazer',
        'Karsu', 'Uncle Sue', 'Chris Allen', 'Myles Sanko', 'Ilse Huizinga', 'Eric Vloeimans en Hotspot',
        'Gare du Nord', 'Rilan & The Bombardiers', 'Soul Six', 'Han Bennink', 'The Nordanians', 'Lilith Merlot',
        'Ruis Soundsystem',
    ],

    'event_card_images' => [
        'Gumbo Kings' => 'Gumbo-king-cover-page-and-event.png',
        'Evolve' => 'Evolve-event.png',
        'Ntjam Rosie' => 'ntjam-rosie-event.png',
        'Wicked Jazz Sounds' => 'Wicked-Jazz-event.png',
        'Wouter Hamel' => 'Wouter-Hamel-event.png',
        'Jonna Frazer' => 'Jonna-fraser-event.png',
        'Karsu' => 'hero-karsu.jpg',
        'Uncle Sue' => 'Uncle-Sue-event.png',
        'Chris Allen' => 'kris-allen-event.png',
        'Myles Sanko' => 'Myles-Sanko-event.png',
        'Ilse Huizinga' => 'Ilse-Huizinga-event.png',
        'Eric Vloeimans en Hotspot' => 'Eric-Vloeimans-and-Hotspot-event.png',
        'Gare du Nord' => 'Gare-du-nord-event.png',
        'Rilan & The Bombardiers' => 'Rilan-&-The-Bombadiers-event.png',
        'Soul Six' => 'Soul-Six-event.png',
        'Han Bennink' => 'Han-Bennink-event.png',
        'The Nordanians' => 'The-Nordanians-event.png',
        'Lilith Merlot' => 'Lilith-Merlot-event.png',
        'Ruis Soundsystem' => 'Ruis-Soundsystem-event.png',
    ],

    'artist_pages' => [
        'gumbo-kings' => [
            'title' => 'Gumbo Kings',
            'tagline' => 'The Groove of New Orleans',
            'hero_image' => 'hero-gumbo-kings.jpg',
        ],
        'karsu' => [
            'title' => 'Karsu',
            'tagline' => 'A symphony of Jazz and Turkish Soul',
            'hero_image' => 'hero-karsu.jpg',
        ],
        'gare-du-nord' => [
            'title' => 'Gare du Nord',
            'tagline' => 'Cinematic Soul from the Urban Lounge',
            'hero_image' => 'hero-gare-du-nord.jpg',
        ],
    ],
];