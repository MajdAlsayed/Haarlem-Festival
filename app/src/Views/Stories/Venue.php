<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
/** @var string $venueHero */

$app = (new \App\Repositories\SettingsRepository())->getAll();

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$venue = $vm->venue;
$venueName = $venue['name'] ?? 'Venue';

$slug = strtolower(trim((string)($_GET['slug'] ?? '')));
$slugSafe = preg_replace('/[^a-z0-9\-]/', '', $slug);

$listToShow  = $vm->stories ?? [];
$exploreMore = $vm->allVenueStories ?? [];
$selectedDay = $vm->selectedDay ?? 'all';

$eventTime = function(array $e): string {
    return (string)($e['start_time'] ?? '');
};

$schuurSplitImage  = '/images/Stories/venues/de-schuur1.jpg';
$schuurGallery1    = '/images/Stories/venues/de-schuur2.jpg';
$schuurGallery2    = '/images/Stories/venues/de-schuur3.jpg';
$schuurGallery3    = '/images/Stories/venues/de-schuur4.jpg';
$schuurExpectImg   = '/images/Stories/venues/de-schuur5.jpg';

$kweekSplitImage   = '/images/Stories/venues/Kweekcafe1.jpg';
$kweekInsideImg1   = '/images/Stories/venues/Kweekcafe2.jpg';
$kweekInsideImg2   = '/images/Stories/venues/Kweekcafe3.jpg';
$kweekInsideImg3   = '/images/Stories/venues/Kweekcafe4.jpg';

$schuurIntroTitle = "Where Stories Come Alive on Stage";
$schuurIntroText  = "De Schuur is one of Haarlem’s most iconic cultural spaces. During Stories in Haarlem, this venue hosts intimate storytelling sessions featuring local voices, history, and hidden tales of the city. The modern interior and warm atmosphere make it the perfect place for immersive storytelling performances.";

$schuurLeftBlocks = [
    [
        'title' => 'THEATRE & CULTURAL STORYTELLING',
        'text'  => 'De Schuur is one of Haarlem’s most important cultural venues, known for its contemporary theatre, film, and storytelling performances. Since its opening, De Schuur has been a place where new voices, experimental formats, and meaningful stories come together.'
    ],
    [
        'title' => 'STORIES, VOICES & SOCIAL THEMES',
        'text'  => 'During Stories in Haarlem, De Schuur hosts intimate storytelling sessions, podcasts, and spoken-word performances. These stories explore personal experiences, social issues, and local history, creating a strong connection between performers and the audience.'
    ],
    [
        'title' => 'A MODERN STAGE FOR STORIES',
        'text'  => 'With its modern architecture and flexible performance spaces, De Schuur offers an atmosphere that feels both open and personal. The venue’s warm interior and excellent acoustics make it ideal for listening-focused events, where every word and emotion matters.'
    ],
];

$schuurWhatTitle = "What to Expect at De-Schuur";
$schuurWhatIntro = "De Schuur offers an intimate and modern setting where stories truly come to life. Expect live podcasts, personal narratives, and thought-provoking performances in a professional theater environment. Each session is carefully scheduled to allow visitors time to reflect, connect, and explore other festival locations.";

$schuurUpcomingTitle = "Upcoming Most Loved Major Events";
$schuurUpcomingText  = "The Omdenken Podcast is a live podcast recording with an audience, hosted at De Schuur. Known for its sharp humor and fresh perspective, the session invites visitors to look at everyday problems in a new way. Combining storytelling, interaction, and reflection, this event offers an engaging cultural experience that fits perfectly within De Schuur’s role as a place for conversation, entertainment, and new ideas.";

$kweekIntroTitle = "A Story Garden in the City";
$kweekIntroText  = "Kweekcafé blends greenery, creativity, and community spirit. Surrounded by plants and natural light, visitors can relax while listening to heartfelt stories told by Haarlemmers from all walks of life. It is a peaceful, inspiring spot where storytelling feels authentic and close to nature. Visitors can unwind, connect, and enjoy intimate storytelling moments shaped by Haarlem’s local voices and warm atmosphere.";

$kweekLeftBlocks = [
    [
        'title' => 'NATURE & HEALTH',
        'text'  => 'KweekCafé sits on the edge of Haarlemmerhout, Haarlem’s oldest public park. Originally part of the historical plant nursery that supplied the city with trees and flowers, this area has long been a place of growth, learning, and quiet reflection. Today, KweekCafé carries this legacy forward as a welcoming space that blends nature, sustainability, and creativity.'
    ],
    [
        'title' => 'COMMUNITY & SUSTAINABILITY',
        'text'  => 'KweekCafé developed into a cultural hub centered around eco-friendly living and community connection. The café operates with a strong focus on local produce, zero-waste principles, and green initiatives. Workshops, meet-ups, and small events take place here throughout the year, making it a gathering spot for Haarlemers who value mindful living and creative expression.'
    ],
    [
        'title' => 'A MODERN STORYTELLING GARDEN',
        'text'  => 'During Stories in Haarlem, KweekCafé transforms into an intimate open-air storytelling stage. Surrounded by garden paths, greenery, and Haarlem’s iconic giant chessboard, visitors listen to tales that blend humor, tradition, and personal memories. The warm atmosphere and natural setting make KweekCafé a unique venue where local stories feel alive, grounded, and deeply connected to the city’s cultural roots.'
    ],
];

$kweekHighlights = [
    ['title' => 'Local food and sustainability', 'text' => 'Exploring environmentally responsible food production in urban settings'],
    ['title' => 'Community-driven initiatives', 'text' => 'How neighbors work together to create lasting change.'],
    ['title' => 'Real stories from Haarlem residents', 'text' => 'Authentic experiences shared by people making a difference'],
    ['title' => 'Impact of small-scale farming', 'text' => 'Understanding how local agriculture shapes our community'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($venueName) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/Stories/venue.css?v=<?= h($app['css_version'] ?? '1') ?>">
</head>

<body class="stories-dark venue-<?= h($slugSafe ?: 'default') ?>">
<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

<?php if (!$venue): ?>
    <div class="wrap-1150 venue-not-found">
        <h1>Venue not found</h1>
        <p>Try: /stories/venue?slug=de-schuur</p>
    </div>
<?php else: ?>

    <?php if ($slugSafe === 'de-schuur'): ?>
        <section class="venue-hero-x"
                 style="background-image:
                 linear-gradient(120deg, rgba(0,0,0,0.38), rgba(0,0,0,0.62)),
                 url('<?= h($venueHero ?? "/images/Stories/venues/default-venue.jpg") ?>');">
            <div class="hero-content">
                <h1>Welcome to De-Schuur</h1>
                <div class="hero-meta">
                    <?= h($venue['address'] ?? '') ?><?= !empty($venue['city']) ? ', ' . h($venue['city']) : '' ?>
                </div>
            </div>
        </section>

        <section class="intro-beige">
            <div class="wrap-1150">
                <h2><?= h($schuurIntroTitle) ?></h2>
                <p><?= h($schuurIntroText) ?></p>
            </div>
        </section>

        <section class="wrap-1150">
            <div class="split">
                <div class="split-copy">
                    <?php foreach ($schuurLeftBlocks as $b): ?>
                        <div class="block">
                            <h3><?= h($b['title']) ?></h3>
                            <p><?= h($b['text']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="split-img">
                    <img src="<?= h($schuurSplitImage) ?>" alt="De Schuur main image">
                </div>
            </div>

            <h2 class="section-title">Select a Day to Attend a Performance</h2>

            <div class="days">
                <?php
                $days = ['all' => 'All Program', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday'];
                foreach ($days as $key => $label):
                    $active = $vm->isActive($key) ? 'active' : '';
                ?>
                    <a class="day <?= $active ?>" href="/stories/venue?slug=<?= h($slug) ?>&day=<?= h($key) ?>">
                        <?= h($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="events">
                <?php if (empty($listToShow)): ?>
                    <p class="events-empty">
                        No stories events found for this venue<?= $selectedDay !== 'all' ? ' on ' . h($selectedDay) : '' ?>.
                    </p>
                <?php else: ?>
                    <?php foreach ($listToShow as $e): ?>
                        <?php
                        $img = $e['image_path'] ?? '/images/Stories/cards/default.jpg';
                        $title = $e['story_name'] ?? $e['title'] ?? '';
                        $type = $e['story_type'] ?? '';
                        $age = $e['age'] ?? '';
                        ?>
                        <div class="event">
                            <div class="event-time">
                                <?= h(ucfirst($e['event_day'] ?? '')) ?><br>
                                <?= h($eventTime($e)) ?>
                            </div>

                            <div class="event-main">
                                <div class="event-thumb">
                                    <img src="<?= h($img) ?>" alt="<?= h($title) ?>">
                                </div>

                                <div class="event-info">
                                    <p class="event-title"><?= h($title) ?></p>
                                    <?php if ($type): ?>
                                        <p class="event-sub"><?= h($type) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="event-side">
                                <?php if ($age): ?>
                                    <div class="event-age">Age <?= h($age) ?></div>
                                <?php endif; ?>

                                <div class="event-actions">
                                    <a class="btnx" href="/stories/detail?id=<?= (int)($e['story_id'] ?? 0) ?>">READ MORE</a>
                                    <a class="btnx primary" href="/tickets">BUY TICKET</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="gallery">
                <img src="<?= h($schuurGallery1) ?>" alt="De Schuur gallery 1">
                <img src="<?= h($schuurGallery2) ?>" alt="De Schuur gallery 2">
                <img src="<?= h($schuurGallery3) ?>" alt="De Schuur gallery 3">
            </div>

            <h2 class="section-title"><?= h($schuurWhatTitle) ?></h2>
            <p class="section-intro">
                <?= h($schuurWhatIntro) ?>
            </p>

            <div class="expect">
                <img src="<?= h($schuurExpectImg) ?>" alt="De Schuur expect image">

                <div class="expect-box">
                    <h3><?= h($schuurUpcomingTitle) ?></h3>
                    <p><?= h($schuurUpcomingText) ?></p>
                </div>
            </div>

            <div class="more-wrap">
                <h2 class="section-title">Event to explore More :</h2>

                <div class="stories-cards-grid">
                    <?php foreach ($exploreMore as $e): ?>
                        <?php
                        $time = $eventTime($e);
                        $img  = $e['image_path'] ?? '/images/Stories/cards/default.jpg';
                        $lang = $e['language'] ?? '';
                        $age  = $e['age'] ?? '';
                        ?>
                        <article class="stories-card">
                            <div class="stories-card-img" style="background-image:url('<?= h($img) ?>')"></div>

                            <div class="stories-card-body">
                                <h3 class="stories-card-title"><?= h($e['story_name'] ?? $e['title'] ?? 'Story') ?></h3>

                                <div class="stories-card-meta">
                                    <div class="meta-row">
                                        <span class="meta-ico">📅</span>
                                        <span><?= h(ucfirst($e['event_day'] ?? '')) ?> <?= h($time) ?></span>
                                    </div>

                                    <div class="meta-row">
                                        <span class="meta-ico">📍</span>
                                        <span><?= h($e['venue_name'] ?? '') ?><?= !empty($e['venue_city']) ? ', ' . h($e['venue_city']) : '' ?></span>
                                    </div>

                                    <?php if ($lang): ?>
                                        <div class="meta-row">
                                            <span class="meta-ico">🌐</span>
                                            <span>Lang: <?= h($lang) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($age): ?>
                                        <div class="meta-row">
                                            <span class="meta-ico">🔞</span>
                                            <span>Age <?= h($age) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <p class="stories-card-desc"><?= h($e['description'] ?? '') ?></p>

                                <div class="stories-card-actions">
                                    <a class="card-btn primary" href="/tickets">BUY TICKETS</a>
                                    <a class="card-btn outline" href="/stories/detail?id=<?= (int)($e['story_id'] ?? 0) ?>">MORE INFO</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    <?php elseif ($slugSafe === 'kweekcafe'): ?>
        <section class="venue-hero-x"
                 style="background-image:
                 linear-gradient(120deg, rgba(0,0,0,0.38), rgba(0,0,0,0.62)),
                 url('<?= h($venueHero ?? "/images/Stories/venues/default-venue.jpg") ?>');">
            <div class="hero-content">
                <h1>Welcome to <?= h($venueName) ?></h1>
                <div class="hero-meta">
                    <?= h($venue['address'] ?? '') ?><?= !empty($venue['city']) ? ', ' . h($venue['city']) : '' ?>
                </div>
            </div>
        </section>

        <section class="intro-beige">
            <div class="wrap-1150">
                <h2><?= h($kweekIntroTitle) ?></h2>
                <p><?= h($kweekIntroText) ?></p>
            </div>
        </section>

        <section class="wrap-1150">
            <div class="split">
                <div class="split-copy">
                    <?php foreach ($kweekLeftBlocks as $b): ?>
                        <div class="block">
                            <h3><?= h($b['title']) ?></h3>
                            <p><?= h($b['text']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="split-img">
                    <img src="<?= h($kweekSplitImage) ?>" alt="Kweekcafe main image">
                </div>
            </div>

            <h2 class="section-title">Select a Day to Attend a Performance</h2>

            <div class="days">
                <?php
                $days = ['all' => 'All Program', 'thursday' => 'Thursday', 'friday' => 'Friday'];
                foreach ($days as $key => $label):
                    $active = $vm->isActive($key) ? 'active' : '';
                ?>
                    <a class="day <?= $active ?>" href="/stories/venue?slug=<?= h($slug) ?>&day=<?= h($key) ?>">
                        <?= h($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="events">
                <?php if (empty($listToShow)): ?>
                    <p class="events-empty">
                        No stories events found for this venue<?= $selectedDay !== 'all' ? ' on ' . h($selectedDay) : '' ?>.
                    </p>
                <?php else: ?>
                    <?php foreach ($listToShow as $e): ?>
                        <?php
                        $img = $e['image_path'] ?? '/images/Stories/cards/default.jpg';
                        $title = $e['story_name'] ?? $e['title'] ?? '';
                        $type = $e['story_type'] ?? '';
                        $age = $e['age'] ?? '';
                        ?>
                        <div class="event">
                            <div class="event-time">
                                <?= h(ucfirst($e['event_day'] ?? '')) ?><br>
                                <?= h($eventTime($e)) ?>
                            </div>

                            <div class="event-main">
                                <div class="event-thumb">
                                    <img src="<?= h($img) ?>" alt="<?= h($title) ?>">
                                </div>

                                <div class="event-info">
                                    <p class="event-title"><?= h($title) ?></p>
                                    <?php if ($type): ?>
                                        <p class="event-sub"><?= h($type) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="event-side">
                                <?php if ($age): ?>
                                    <div class="event-age">Age <?= h($age) ?></div>
                                <?php endif; ?>

                                <div class="event-actions">
                                    <a class="btnx" href="/stories/detail?id=<?= (int)($e['story_id'] ?? 0) ?>">READ MORE</a>
                                    <a class="btnx primary" href="/tickets">BUY TICKET</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <h2 class="section-title">Inside the Garden Café</h2>

            <div class="kweek-inside-grid">
                <div class="kweek-inside-card">
                    <img src="<?= h($kweekInsideImg1) ?>" alt="Inside Kweekcafe 1">
                    <div class="kweek-inside-text">
                        Surrounded by greenery and hidden paths, Kweekcafé invites visitors to slow down and rediscover Haarlem through fresh perspectives.
                        With views into the nursery area, quiet seating, and a relaxed atmosphere, the venue feels like a living room inside a garden.
                    </div>
                </div>

                <div class="kweek-inside-card">
                    <img src="<?= h($kweekInsideImg2) ?>" alt="Inside Kweekcafe 2">
                    <div class="kweek-inside-text">
                        Every visit to Kweekcafé brings new stories — from eco-friendly ideas and urban gardening to personal reflections.
                        Its natural setting transforms each performance into an intimate moment where Haarlem’s local voices feel close and real.
                    </div>
                </div>
            </div>

            <h2 class="section-title">Key Themes & Highlights</h2>

            <div class="highlights-grid">
                <?php foreach ($kweekHighlights as $x): ?>
                    <div class="highlight-card">
                        <div class="highlight-icon">✓</div>
                        <div>
                            <div class="highlight-title"><?= h($x['title']) ?></div>
                            <div class="highlight-text"><?= h($x['text']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="more-wrap">
                <h2 class="section-title">Event to explore More :</h2>

                <div class="stories-cards-grid">
                    <?php foreach ($exploreMore as $e): ?>
                        <?php
                        $time = $eventTime($e);
                        $img  = $e['image_path'] ?? '/images/Stories/cards/default.jpg';
                        $lang = $e['language'] ?? '';
                        $age  = $e['age'] ?? '';
                        ?>
                        <article class="stories-card">
                            <div class="stories-card-img" style="background-image:url('<?= h($img) ?>')"></div>

                            <div class="stories-card-body">
                                <h3 class="stories-card-title"><?= h($e['story_name'] ?? $e['title'] ?? 'Story') ?></h3>

                                <div class="stories-card-meta">
                                    <div class="meta-row">
                                        <span class="meta-ico">📅</span>
                                        <span><?= h(ucfirst($e['event_day'] ?? '')) ?> <?= h($time) ?></span>
                                    </div>

                                    <div class="meta-row">
                                        <span class="meta-ico">📍</span>
                                        <span><?= h($e['venue_name'] ?? '') ?><?= !empty($e['venue_city']) ? ', ' . h($e['venue_city']) : '' ?></span>
                                    </div>

                                    <?php if ($lang): ?>
                                        <div class="meta-row">
                                            <span class="meta-ico">🌐</span>
                                            <span>Lang: <?= h($lang) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($age): ?>
                                        <div class="meta-row">
                                            <span class="meta-ico">🔞</span>
                                            <span>Age <?= h($age) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <p class="stories-card-desc"><?= h($e['description'] ?? '') ?></p>

                                <div class="stories-card-actions">
                                    <a class="card-btn primary" href="/tickets">BUY TICKETS</a>
                                    <a class="card-btn outline" href="/stories/detail?id=<?= (int)($e['story_id'] ?? 0) ?>">MORE INFO</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    <?php else: ?>
        <div class="wrap-1150 venue-not-found">
            <h1><?= h($venueName) ?></h1>
            <p>This venue does not have a custom design yet.</p>
        </div>
    <?php endif; ?>

<?php endif; ?>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>