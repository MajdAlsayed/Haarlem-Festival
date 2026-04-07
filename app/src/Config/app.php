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
    'css_version' => '27',

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

    /** Absolute site URL for Stripe redirects (e.g. http://localhost or https://yourdomain.nl). */
    'public_base_url' => rtrim((string) (getenv('APP_PUBLIC_URL') ?: 'http://localhost'), '/'),

    /**
     * Homepage hero / welcome / about copy. Overridden by site_settings keys `cms_home_*` (see SettingsRepository::getMergedCmsHome).
     *
     * @var array<string, string>
     */
    'cms_home' => [
        'hero_eyebrow' => '',
        'hero_heading' => 'Haarlem Festival',
        'hero_subtitle' => '',
        'hero_cta_label' => 'Explore',
        'hero_cta_href' => '#events',
        'welcome_heading' => '',
        'welcome_p1' => '',
        'welcome_p2' => '',
        'about_heading' => '',
        'about_text' => '',
        'about_image_src' => '/images/About-haarlem.jpg',
        'about_image_alt' => '',
        'about_more_label' => '',
        'about_more_href' => '#',
        'events_heading' => 'Upcoming Festival and Events',
        'events_subtitle' => 'Music, jazz, dance, history, food, and stories — pick a path and get tickets.',
        'events_info_label' => 'INFO >',
        'events_tickets_label' => 'TICKETS >',
        'events_category_dance' => 'Dance',
        'events_category_jazz' => 'Jazz',
        'events_category_history' => 'History',
        'events_category_yammy' => 'Food',
        'events_category_stories' => 'Stories',
        'expect_heading' => '',
        'expect_intro' => '',
        'expect_subheading' => '',
        'expect_cta' => '',
        'expect_cards_json' => '[]',
    ],
];
