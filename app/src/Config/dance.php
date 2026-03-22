<?php

declare(strict_types=1);

/**
 * Dance page: image filenames (in /public/images/dance/), genres, featured first card override, artists.
 */
return [
    /** Shown in &lt;h1&gt; and &lt;title&gt; (editable via /admin/cms/dance). */
    'dance_page_title' => 'Dance Festival',
    'about_section_heading' => 'About Dance',
    'featured_section_title' => 'Featured Events',
    'all_events_section_title' => 'All Events',
    'artists_section_title' => 'Artist(s)',
    'hero_cta_label' => 'View Dance Events',
    'hero_subtitle' => 'Experience Haarlem\'s biggest nights of house, techno, and trance.',
    'about_paragraphs' => [
        'Haarlem Dance brings the world\'s best house, techno and trance DJs to iconic Haarlem locations.',
        'Across three nights, visitors experience Back2Back headline sets, immersive club sessions and unique experimental performances.',
        'Join thousands of music lovers for the most energetic part of the Festival.',
    ],

    'hero_image' => 'Dance page front picture.png',
    'featured_images' => ['Dance-page-1.png', 'Dance-page-2.png', 'Dance-page-3.png'],
    'friday_images' => ['dance-page-friday-1.png', 'dance-page-friday-2.png', 'dance-page-friday-3.png', 'dance-page-friday-4.png', 'dance-page-friday-5.png'],
    'saturday_images' => ['dance-page-satuday-1.png', 'dance-page-satuday-2.png', 'dance-page-satuday-3.png', 'dance-page-satuday-4.png'],
    'sunday_images' => ['dance-page-sunday-1.png', 'dance-page-sunday-2.png', 'dance-page-sunday-3.png', 'dance-page-sunday-4.png'],

    'friday_genres' => ['HOUSE', 'TRANCE', 'DANCE', 'TRANCE', 'ELECTRONIC'],
    'saturday_genres' => ['MIXED GENRES', 'HOUSE', 'TRANCE / ELECTRO', 'ELECTROHOUSE'],
    'sunday_genres' => ['MIXED GENRES', 'TRANCE', 'DANCE', 'ELECTRONIC'],

    'featured_genre_labels' => ['HOUSE', 'TRANCE', 'DANCE'],

    'venue_order_friday' => [4, 7, 5, 8, 9],
    'venue_order_saturday' => [6, 5, 7],
    'venue_order_sunday' => [6, 5, 8, 7],

    'artists' => [
        [
            'slug' => 'hardwell',
            'image' => 'Image (Robbert Hardwell).png',
            'name' => 'Robbert Hardwell',
            'bio' => 'A high-energy dance night featuring Hardwell\'s signature big-room sound, explosive drops, and immersive festival-style atmosphere.',
        ],
        [
            'slug' => 'tiesto',
            'image' => 'Image (Tiësto).png',
            'name' => 'Tiësto',
            'bio' => 'A signature Tiësto club night featuring his blend of trance, techno, and electronic energy inside Haarlem\'s Slachthuis.',
        ],
    ],

    'artist_music' => [
        'hardwell' => [
            'display_name' => 'HARDWELL',
            'real_name' => 'Robbert Hardwell',
            'location' => 'Breda, Netherlands',
            'album_title' => 'Hardwell & Friends Vol. 04',
            'album_sub' => 'VOL. 04',
            'tracks' => [
                ['title' => 'The Partycrasher', 'duration' => '2:54', 'audio' => '/audio/Hardwell & Chuckie - The Partycrasher (Hardwell & Friends Vol. 04).mp3'],
                ['title' => 'Lights Out', 'duration' => '4:54', 'audio' => '/audio/Hardwell & Olly James - Lights Out (Hardwell & Friends Vol. 04).mp3'],
                ['title' => 'Rise Again', 'duration' => '2:78', 'audio' => '/audio/Hardwell & Ryos - Rise Again (Hardwell & Friends Vol. 04).mp3'],
            ],
            'extra_tracks' => [
                ['artist' => 'Hardwell, Dyro', 'title' => 'Not Alone', 'tag' => 'Dance', 'cover' => '/images/dance/Artist/hardwell3.jpg', 'audio' => '/audio/Hardwell & Dyro - Not Alone (Official Music Video).mp3'],
                ['artist' => 'Hardwell, Maddix', 'title' => 'Rave Till My Grave (feat. Villain)', 'tag' => 'Dance', 'cover' => '/images/dance/Artist/hardwell4.jpg', 'audio' => '/audio/Hardwell & Maddix feat. Villain - Rave Till My Grave.mp3'],
            ],
            'about' => [
                'Hardwell is one of the Netherlands\' biggest electronic music names, known for his energetic mainstage sound and powerful club performances.',
                'His sets combine festival-level intensity with sharp, modern dance drops making him a guaranteed crowd favorite at Haarlem Dance 2026.',
            ],
            'highlights' => [
                'Headliner at major festivals including Tomorrowland and Ultra',
                'Voted #1 DJ in the World twice by DJ Mag',
                'Founder of Revealed Recordings',
            ],
            'gallery_stats' => [['num' => '250+', 'label' => 'PHOTOS'], ['num' => '500+', 'label' => 'LIVE SHOWS'], ['num' => '100+', 'label' => 'FESTIVALS'], ['num' => '80+', 'label' => 'COUNTRIES']],
        ],
        'tiesto' => [
            'display_name' => 'TIËSTO',
            'real_name' => 'Tiësto',
            'location' => 'The World is My Home',
            'album_title' => 'Tiësto',
            'album_sub' => 'Featured',
            'tracks' => [
                ['title' => 'RVN (Raven)', 'duration' => '3:24', 'audio' => '/audio/Tiësto - RVN (Raven).mp3'],
                ['title' => 'Drifting (Arodes Remix)', 'duration' => '4:12', 'audio' => '/audio/Tiësto - Drifting (Official Music Video).mp3'],
                ['title' => 'Everlight', 'duration' => '5:24', 'audio' => '/audio/Tiësto Mathame - Everlight (Official Audio).mp3'],
            ],
            'extra_tracks' => [],
            'about' => [
                'Tiësto is one of the most influential electronic artists in the world. Known for his signature blend of trance, electro, and festival-ready sounds.',
                'His emotional melodies, powerful drops, and decades of experience make him a global dance icon.',
            ],
            'highlights' => [
                'Grammy Award-winning DJ & producer',
                'Performed at the Olympics Opening Ceremony (Athens 2004)',
                'Known for legendary albums like Just Be and Elements of Life',
            ],
            'gallery_stats' => [['num' => '300+', 'label' => 'PHOTOS'], ['num' => '700+', 'label' => 'LIVE SHOWS'], ['num' => '150+', 'label' => 'FESTIVALS'], ['num' => '60+', 'label' => 'COUNTRIES']],
        ],
    ],
];
