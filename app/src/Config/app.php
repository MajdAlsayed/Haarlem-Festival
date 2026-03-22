<?php

declare(strict_types=1);

return [
    'site_name' => 'Haarlem Festival',
    'home_path' => '/',
    'logo_filename' => 'Logo.png',
    'logo_src' => '/images/jazz/Logo.jpg',
    'icons_path' => '/images/icons/',
    'default_event_location' => 'Haarlem — Netherlands',
    'default_venue_city' => 'Haarlem',
    'default_event_time' => '22:00',
    'css_version' => '20',

    'footer' => [
        'social_icons' => ['insta icon.png', 'tiktok icon.png', 'facebook icon.png', 'youtube icon.png'],
        'app_icons' => ['apple.png', 'google play.png'],
        'app_labels' => ['App Store', 'Google Play'],
    ],

    'venue_coordinates' => [
        'Caprera Openluchttheater' => [52.4112, 4.6062],
        'Jopenkerk' => [52.3813, 4.6368],
        'Lichtfabriek' => [52.3890, 4.6330],
        'Patronaat' => [52.3820, 4.6380],
        'XO the Club' => [52.3815, 4.6370],
        'Slachthuis' => [52.3825, 4.6350],
    ],

    'day_labels' => [
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ],

    /** Used by SecureToken::signTicketCode for QR / scanner verification (override via env in production). */
    'ticket_signing_secret' => getenv('HAARLEM_TICKET_SECRET') ?: 'dev-only-change-in-production',

    /**
     * Homepage hero / welcome / about copy. Overridden by site_settings keys `cms_home_*` (see SettingsRepository::getMergedCmsHome).
     *
     * @var array<string, string>
     */
    'cms_home' => [
        'hero_eyebrow' => 'Festival',
        'hero_heading' => 'Haarlem Festival',
        'hero_subtitle' => 'Experience music, culture & history like never before.',
        'hero_cta_label' => 'Explore Now',
        'hero_cta_href' => '#events',
        'welcome_heading' => 'Welcome to Haarlem Festival',
        'welcome_p1' => 'Celebrate the heart of Haarlem with a unique fusion of music, culture, history, and food. Across several days, the city transforms into an open-air stage filled with live performances, guided tours, local cuisine, workshops, and unforgettable nightlife.',
        'welcome_p2' => 'Whether you\'re here for jazz, dance, storytelling, or historical experiences, the Haarlem Festival brings the entire city together for a vibrant celebration for all ages.',
        'about_heading' => 'About Haarlem Festival',
        'about_text' => 'The Haarlem Festival brings together music, culture, food, and centuries of Dutch heritage. Experience a unique blend of history and modern entertainment at the heart of Haarlem.',
        'about_image_src' => '/images/About-haarlem.jpg',
        'about_image_alt' => 'Haarlem Festival',
        'about_more_label' => 'MORE INFO >',
        'about_more_href' => '#',
    ],
];
