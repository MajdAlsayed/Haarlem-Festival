<?php

declare(strict_types=1);

/**
 * Default Dance content and image filenames; CMS overrides most keys in the database. artist_music enriches artist pages.
 */
return [
    /** Shown in &lt;h1&gt; and &lt;title&gt; (editable via /admin/cms/dance). */
    'dance_page_title' => 'Dance',
    'about_section_heading' => 'Haarlem Dance',
    'featured_section_title' => 'Featured events',
    'all_events_section_title' => 'All events',
    'artists_section_title' => 'Artists',
    'hero_cta_label' => 'See featured events',
    'hero_subtitle' => '<p>Electronic music across three nights — venues, passes, and headline sets.</p>',
    'about_paragraphs' => [
        '<p>Haarlem Dance brings DJs and live electronic acts to iconic venues. Browse by day, grab tickets or passes, and explore artist profiles.</p>',
    ],

    'hero_image' => 'Dance page front picture.png',
    'featured_images' => [],
    'friday_images' => [],
    'saturday_images' => [],
    'sunday_images' => [],

    'friday_genres' => [],
    'saturday_genres' => [],
    'sunday_genres' => [],

    'featured_genre_labels' => [],
    'breadcrumb_home_label' => 'HOME',
    'breadcrumb_dance_label' => 'DANCE',
    'day_label_friday' => 'Friday',
    'day_label_saturday' => 'Saturday',
    'day_label_sunday' => 'Sunday',
    'artist_info_label' => 'INFO >',
    'show_more_artists_label' => 'Show More Artists >',

    'venue_order_friday' => [4, 7, 5, 8, 9],
    'venue_order_saturday' => [6, 5, 7],
    'venue_order_sunday' => [6, 5, 8, 7],

    /** Slugs shown on /dance (homepage strip). Must match rows in `artists` for detail pages. Overridable via CMS `artists` JSON. */
    'dance_index_artist_slugs' => ['hardwell', 'tiesto'],

    /**
     * Default homepage artist cards when CMS `artists` is empty or []. Keep in sync with ArtistsSeeder / `artists` table.
     *
     * @var list<array{name: string, slug: string, bio: string, image: string}>
     */
    'artists' => [
        [
            'name' => 'Robbert Hardwell',
            'slug' => 'hardwell',
            'bio' => 'A high-energy dance night featuring Hardwell\'s signature big-room sound, explosive drops, and immersive festival-style atmosphere.',
            'image' => 'Artist/hardwell1.png',
        ],
        [
            'name' => 'Tiësto',
            'slug' => 'tiesto',
            'bio' => 'A signature Tiësto club night featuring his blend of trance, techno, and electronic energy inside Haarlem\'s Slachthuis.',
            'image' => 'Image (Tiësto).png',
        ],
    ],
    /**
     * Rich artist detail content for /dance/artist/{slug}. Add MP3s under public/audio/ (see public/audio/README.txt).
     *
     * @var array<string, array<string, mixed>>
     */
    'artist_music' => [
        'hardwell' => [
            'hero_tagline' => 'High-energy dance',
            'follow_url' => 'https://open.spotify.com/artist/6Brvow44BowRkBHKq5FJmn',
            'about' => [
                'Robbert van de Corput — known worldwide as Hardwell — helped define modern big-room and festival dance music. His sets blend anthem melodies, driving kicks, and festival-scale energy that has filled arenas and main stages for over a decade.',
                'Beyond the booth, he founded Revealed Recordings, championing new talent and a steady stream of club and festival weapons. Expect immersive drops, laser-sharp production, and a crowd-first atmosphere at Haarlem Dance.',
            ],
            'highlights' => [
                'Two-time DJ Mag #1 DJ (2013–2014)',
                'Founder of Revealed Recordings and Revealed events worldwide',
                'Headlined Tomorrowland, Ultra Music Festival, and EDC Las Vegas',
                'Iconic singles and remixes shaping the big-room era',
                'Collaborations with Tiësto, Armin van Buuren, and Afrojack',
            ],
            'display_name' => 'HARDWELL',
            'real_name' => 'Robbert van de Corput',
            'location' => 'Breda, Netherlands',
            'album_title' => 'Hardwell & Friends Vol. 3',
            'album_sub' => 'EP • 2025',
            'tracks' => [
                ['title' => 'The Partycrasher', 'duration' => '3:24', 'audio' => '/audio/partycrasher.mp3'],
                ['title' => 'Lights Out', 'duration' => '3:08', 'audio' => '/audio/lights-out.mp3'],
                ['title' => 'Rise Again', 'duration' => '3:41', 'audio' => '/audio/rise-again.mp3'],
                ['title' => 'Not Alone', 'duration' => '3:55', 'audio' => '/audio/not-alone.mp3'],
                ['title' => 'Rave Till My Grave', 'duration' => '3:12', 'audio' => '/audio/rave-till-my-grave.mp3'],
            ],
            'extra_tracks' => [
                [
                    'artist' => 'HARDWELL',
                    'title' => 'Spaceman',
                    'cover' => 'Artist/hardwell3.jpg',
                    'audio' => '/audio/lights-out.mp3',
                    'tag' => 'Dance',
                ],
            ],
            'gallery_stats' => [
                ['num' => '250+', 'label' => 'SHOWS'],
                ['num' => '500+', 'label' => 'TRACKS'],
                ['num' => '100+', 'label' => 'ALBUMS'],
                ['num' => '80+', 'label' => 'AWARDS'],
            ],
            /** Fallback when `artist_photos` has fewer than four rows (same paths as ArtistPhotosSeeder). */
            'gallery' => [
                'Artist/hardwell1.png',
                'Artist/hardwell7.png',
                'Artist/hardwell8.png',
                'Artist/hardwell9.png',
            ],
        ],
        'tiesto' => [
            'hero_tagline' => 'Trance and electro',
            'follow_url' => 'https://open.spotify.com/artist/2CIMQHJaSUzqSkFPaQNAEQ',
            'about' => [
                'Tiësto is a cornerstone of electronic music — from trance anthems to chart-topping collaborations. His sets move between melodic tension and peak-time energy, built for big rooms and late nights.',
                'At Haarlem Dance, expect precision mixing, recognizable hooks, and a journey through club and festival favourites — all inside the industrial atmosphere of Slachthuis.',
            ],
            'highlights' => [
                'Grammy-winning producer and global touring artist',
                'Pioneered the stadium-trance sound; evolved into house and pop crossover hits',
                'Residencies and headline slots at major festivals worldwide',
                'Collaborations with artists across pop and electronic music',
                'Decades of releases spanning trance, progressive, and modern club music',
            ],
            'display_name' => 'TIËSTO',
            'real_name' => 'Tijs Michiel Verwest',
            'location' => 'Netherlands',
            'album_title' => 'Drive',
            'album_sub' => 'Album • 2023',
            'tracks' => [
                ['title' => 'The Business', 'duration' => '2:44', 'audio' => '/audio/partycrasher.mp3'],
                ['title' => 'Jackie Chan', 'duration' => '2:35', 'audio' => '/audio/lights-out.mp3'],
                ['title' => 'On My Way', 'duration' => '3:12', 'audio' => '/audio/rise-again.mp3'],
                ['title' => 'Red Lights', 'duration' => '3:20', 'audio' => '/audio/not-alone.mp3'],
            ],
            'extra_tracks' => [
                [
                    'artist' => 'TIËSTO',
                    'title' => 'Adagio for Strings',
                    'cover' => 'Artist/tiesto3.png',
                    'audio' => '/audio/rave-till-my-grave.mp3',
                    'tag' => 'Trance',
                ],
            ],
            'gallery_stats' => [
                ['num' => '300+', 'label' => 'SHOWS'],
                ['num' => '600+', 'label' => 'TRACKS'],
                ['num' => '120+', 'label' => 'ALBUMS'],
                ['num' => '90+', 'label' => 'AWARDS'],
            ],
            'gallery' => [
                'Artist/tiesto4.png',
                'Artist/tiesto5.png',
                'Artist/tiesto6.png',
                'Artist/tiesto7.png',
            ],
        ],
    ],
];
