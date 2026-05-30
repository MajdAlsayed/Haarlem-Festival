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
    'css_version' => '28',

    'footer' => [
        'social_icons' => ['insta icon.png', 'tiktok icon.png', 'facebook icon.png', 'youtube icon.png'],
        'app_icons' => ['apple.png', 'google play.png'],
        'app_labels' => ['App Store', 'Google Play'],
    ],

    /** Used by SecureToken::signTicketCode for QR / scanner verification (override via env in production). */
    'ticket_signing_secret' => getenv('HAARLEM_TICKET_SECRET') ?: 'dev-only-change-in-production',

    /** Absolute site URL for Stripe redirects (e.g. http://localhost or https://yourdomain.nl). */
    'public_base_url' => rtrim((string) (getenv('APP_PUBLIC_URL') ?: 'http://localhost'), '/'),

    /** Google reCAPTCHA v2. Replace env vars with real keys in production. Test keys always pass. */
    'recaptcha_site_key'   => getenv('RECAPTCHA_SITE_KEY')   ?: '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
    'recaptcha_secret_key' => getenv('RECAPTCHA_SECRET_KEY') ?: '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',

    /**
     * Homepage hero / welcome / about copy. Overridden by site_settings keys `cms_home_*` (see SettingsRepository::getMergedCmsHome).
     *
     * @var array<string, string>
     */
    'cms_home' => [
        'hero_eyebrow' => '',
        'hero_heading' => 'Haarlem Festival',
        'hero_subtitle' => '<p>Five days of music, food, culture, and stories across the city.</p>',
        'hero_cta_label' => 'Explore',
        'hero_cta_href' => '#events',
        'welcome_heading' => '',
        'welcome_p1' => '',
        'welcome_p2' => '',
        'about_heading' => 'Discover Haarlem Festival',
        'about_text' => '<p>From jazz clubs and dance nights to history walks, food experiences, and immersive stories — the festival turns Haarlem into a stage for everyone.</p><p>Browse by theme, build your programme, and book tickets in one place.</p>',
        'about_image_src' => '/images/About-haarlem.jpg',
        'about_image_alt' => 'Historic Haarlem cityscape',
        'about_more_label' => 'See all events',
        'about_more_href' => '#events',
        'events_heading' => 'Upcoming Festival and Events',
        'events_subtitle' => 'Music, jazz, dance, history, food, and stories — pick a path and get tickets.',
        'events_info_label' => 'INFO >',
        'events_tickets_label' => 'TICKETS >',
        'events_category_dance' => 'Dance',
        'events_category_jazz' => 'Jazz',
        'events_category_history' => 'History',
        'events_category_yammy' => 'Food',
        'events_category_stories' => 'Stories',
        'expect_heading' => 'What to expect',
        'expect_intro' => 'Plan your days with clear routes, tickets, and venues — whether you want one highlight or the full weekend.',
        'expect_subheading' => 'Highlights by experience',
        'expect_cta' => 'Use the category cards above to jump to jazz, dance, history, food, or stories.',
        'expect_cards_json' => json_encode([
            ['icon' => '🎵', 'title' => 'Music & nights', 'items' => ['Jazz stages and halls', 'Dance at Slachthuis', 'Stories performances']],
            ['icon' => '🍽️', 'title' => 'Food & Yammy', 'items' => ['Restaurant trail', 'Tastings', 'City bites']],
            ['icon' => '🏛️', 'title' => 'Culture', 'items' => ['Guided history tours', 'Landmarks', 'Family-friendly routes']],
            ['icon' => '🎺', 'title' => 'Jazz', 'items' => ['Patronaat, Schuur & more', 'Filter by day on /jazz', 'Artist pages & audio previews']],
            ['icon' => '🎛️', 'title' => 'Dance', 'items' => ['Slachthuis headline nights', 'Day passes & weekend pass', 'Hardwell, Tiësto & lineup']],
            ['icon' => '📖', 'title' => 'Stories', 'items' => ['Venue-based routes', 'Detail pages per story', 'Bundle with festival tickets']],
        ], JSON_UNESCAPED_UNICODE),
    ],
];
