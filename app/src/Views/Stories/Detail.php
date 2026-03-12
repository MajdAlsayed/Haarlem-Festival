<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
$app = (new \App\Repositories\SettingsRepository())->getAll();

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$story = $vm->story;

$storyName = strtolower(trim((string)($story['name'] ?? '')));
$storySlug = strtolower(trim((string)($story['slug'] ?? '')));

$isOmdenken = in_array($storySlug, ['omdenken-podcast'], true)
    || in_array($storyName, ['omdenken podcast'], true);

$isBuurderij = in_array($storySlug, ['the-story-of-buurderij-haarlem'], true)
    || in_array($storyName, ['the story of buurderij haarlem'], true);

$heroImg = $story['image_path'] ?? '/images/Stories/cards/default.jpg';
$venueText = trim((string)($story['venue_name'] ?? '') . (!empty($story['venue_city']) ? ', ' . $story['venue_city'] : ''));
$dayText   = ucfirst((string)($story['event_day'] ?? ''));
$timeText  = (string)($story['start_time'] ?? '');
$ageText   = (string)($story['age'] ?? '');
$langText  = (string)($story['language'] ?? '');
$typeText  = (string)($story['story_type'] ?? '');
$descText  = (string)($story['description'] ?? '');

$omdenkenHero      = '/images/Stories/details/omdenken-hero.jpg';
$omdenkenBlock1    = '/images/Stories/details/omdenken2.jpg';
$omdenkenBlock2    = '/images/Stories/details/omdenken3.jpg';
$omdenkenPoster    = '/images/Stories/details/omdenken1.jpg';

$buurderijHero     = '/images/Stories/details/Kweekcafehero.jpg';
$buurderijMain     = '/images/Stories/details/Kweekcafe1.jpg';
$buurderijMain2     = '/images/Stories/details/Kweekcafe2.jpg';
$buurderijGallery1 = '/images/Stories/details/Kweekcafeg1.jpg';
$buurderijGallery2 = '/images/Stories/details/Kweekcafeg2.jpg';
$buurderijGallery3 = '/images/Stories/details/Kweekcafeg3.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($vm->pageTitle ?? 'Story Detail') ?></title>

  <link rel="stylesheet" href="/css/style.css?v=<?= h($app['css_version'] ?? '1') ?>">
  <link rel="stylesheet" href="/css/Stories/details.css?v=<?= h($app['css_version'] ?? '1') ?>">
</head>
<body class="stories-detail-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="detail-page">

  <?php if (!$story): ?>
    <h1 class="detail-not-found">Story not found</h1>

  <?php elseif ($isOmdenken): ?>
    <section class="omdenken-page">
      <section class="omdenken-hero">
        <div class="omdenken-hero-image">
          <img src="<?= h($omdenkenHero) ?>" alt="Omdenken live podcast">
          <div class="omdenken-hero-overlay"></div>

          <div class="omdenken-hero-copy">
            <h1 class="omdenken-title">OMDENKEN – LIVE PODCAST SESSION</h1>
            <p class="omdenken-subtitle">
              Changing perspectives through real stories.
            </p>
            <p class="omdenken-hero-text">
              This live podcast session invites visitors to explore how everyday challenges can be transformed into meaningful
              insights. Through honest conversations and personal experiences, the speakers share stories that inspire reflection,
              resilience, and new ways of thinking.
            </p>
          </div>
        </div>
      </section>

      <section class="omdenken-content">
        <div class="omdenken-top-grid">
          <div class="omdenken-poster-card">
            <img src="<?= h($omdenkenPoster) ?>" alt="Podcasts met als onderwerp Werk">
          </div>

          <div class="omdenken-event-meta">
            <div class="omdenken-meta-line"><span>Venue :</span> <?= h($venueText ?: 'De Schuur, Haarlem') ?></div>
            <div class="omdenken-meta-line"><span>Age Group :</span> <?= h($ageText ?: '16+') ?></div>
            <div class="omdenken-meta-line"><span>Date :</span> July 23 , 2026</div>
            <div class="omdenken-meta-line"><span>Time:</span> 9:00 - 20:15</div>
          </div>
        </div>

        <div class="omdenken-story-grid">
          <div class="omdenken-text-block">
            <h3>What you'll experience</h3>
            <ul class="omdenken-bullet-list">
              <li>Real-life stories from inspiring speakers.</li>
              <li>Fresh perspectives on personal and social challenges.</li>
              <li>Thought-provoking conversations with practical takeaways.</li>
              <li>A relaxed, intimate atmosphere for listening and reflection.</li>
            </ul>
          </div>

          <div class="omdenken-text-block omdenken-text-block-right">
            <p class="omdenken-right-copy">
              Omdenken gives you practical tools to reframe frustration, find new angles, and turn stuck situations into
              surprising opportunities. You'll walk away with ideas you can apply immediately, at work, at home, or in your
              personal life.
            </p>
          </div>
        </div>

        <div class="omdenken-story-grid omdenken-story-grid-second">
          <div class="omdenken-text-block">
            <h3>Why this story matters</h3>
            <p>
              This session goes beyond entertainment. It offers space to slow down, listen, and connect with real experiences
              from people in Haarlem and beyond. Each story encourages empathy, curiosity, and a deeper understanding of
              everyday life.
            </p>
          </div>

          <div class="omdenken-host-image-wrap">
            <img src="<?= h($omdenkenBlock1) ?>" alt="Praktijkvader podcast">
          </div>
        </div>

        <div class="omdenken-player-section">
          <div class="omdenken-player-poster">
            <img src="<?= h($omdenkenBlock2) ?>" alt="Omdenken visual">

            <div class="omdenken-poster-caption">
              <strong>Haarlem Nights</strong>
              <span>The Podcast Collective</span>
            </div>
          </div>

          <div class="omdenken-player-ui">
            <div class="player-top-row">
              <button type="button" class="player-mini-btn">×</button>
              <button type="button" class="player-mini-btn">◀</button>
              <button type="button" class="player-play-btn">▶</button>
              <button type="button" class="player-mini-btn">▶</button>
              <button type="button" class="player-mini-btn">↻</button>
            </div>

            <div class="player-time-row">
              <span>0:00</span>
              <span>4:03</span>
            </div>

            <div class="player-progress-wrap">
              <div class="player-progress-bar">
                <div class="player-progress-fill"></div>
              </div>
            </div>

            <div class="player-volume-row">
              <span>♡</span>
              <div class="player-volume-bar">
                <div class="player-volume-fill"></div>
              </div>
              <span>🔊</span>
            </div>

            <div class="player-recent-title">Recent Podcast</div>

            <div class="player-tracklist">
              <div class="track-row active">
                <div class="track-info">
                  <strong>Flip Thinking Podcast</strong>
                  <small>The Storymakers</small>
                </div>
                <span>1:10</span>
              </div>

              <div class="track-row">
                <div class="track-info">
                  <strong>Haarlem Special</strong>
                  <small>Thursday Sessions</small>
                </div>
                <span>2:11</span>
              </div>
            </div>
          </div>
        </div>

        <div class="omdenken-stats-row">
          <div class="omdenken-stat-card">
            <div class="omdenken-stat-icon">🕒</div>
            <div class="omdenken-stat-title">DURATION</div>
            <div class="omdenken-stat-value">75 Min.</div>
          </div>

          <div class="omdenken-stat-card">
            <div class="omdenken-stat-icon">👥</div>
            <div class="omdenken-stat-title">Haarlem Pass</div>
            <div class="omdenken-stat-value">25% Discount</div>
          </div>

          <div class="omdenken-stat-card">
            <div class="omdenken-stat-icon">📍</div>
            <div class="omdenken-stat-title">PRICE</div>
            <div class="omdenken-stat-value">FROM € 12.5</div>
          </div>
        </div>

        <div class="omdenken-note-row">
          <div class="omdenken-note-label">Note:</div>
          <div class="omdenken-note-text">
            This is a live podcast recording with a limited number of seats.<br>
            Reservation is required to guarantee entry.
          </div>
        </div>

        <div class="omdenken-ticket-row">
          <a class="omdenken-ticket-btn" href="/tickets">BUY TICKETS</a>
        </div>
      </section>
    </section>


/*////// The Story of Buurderij Haarlem/////*/


<?php elseif ($isBuurderij): ?>
  <section class="buurderij-page">
    <section class="buurderij-hero">
      <div class="buurderij-hero-image">
        <img src="<?= h($buurderijHero) ?>" alt="The Story of Buurderij Haarlem">
        <div class="buurderij-hero-overlay"></div>

        <div class="buurderij-hero-copy">
          <h1 class="buurderij-title">The Story of Buurderij Haarlem</h1>
        </div>
      </div>
    </section>

    <section class="buurderij-content">
      <section class="buurderij-intro">
        <h2>What is “The Story of Buurderij Haarlem”?</h2>
        <p>
          This story highlights how a local food initiative can become a meaningful social experience for the whole city.
          Through Buurderij Haarlem, visitors discover the connection between producers, residents, and everyday choices around
          food, sustainability, and community. It is a story about sharing values, supporting local makers, and building trust
          through direct contact.
        </p>
      </section>

      <section class="buurderij-main-grid">
        <div class="buurderij-main-image">
          <img src="<?= h($buurderijMain) ?>" alt="Buurderij Haarlem main scene">
        </div>

        <div class="buurderij-main-text">
          <h3>The Story Behind Buurderij Haarlem</h3>
          <p>
            Buurderij Haarlem brings together local farmers, food makers, and residents in one welcoming place. The project
            makes food feel more personal and more transparent, because visitors meet the people behind the products directly.
            Instead of being only a market, it becomes a place where conversation, trust, and shared responsibility grow.
          </p>
          <p>
            This story shows how local systems can strengthen a city socially as well as economically. In Haarlem, Buurderij
            reflects a wider movement toward sustainability, human connection, and community-based living.
          </p>
        </div>
      </section>

      <section class="buurderij-gallery-grid">
        <article class="buurderij-gallery-card">
          <img src="<?= h($buurderijGallery1) ?>" alt="Local food choices">
          <h4>Local food choices</h4>
          <p>
            Fresh products, short supply chains, and meaningful contact between producers and residents.
          </p>
        </article>

        <article class="buurderij-gallery-card">
          <img src="<?= h($buurderijGallery2) ?>" alt="Community gathering">
          <h4>Community gathering</h4>
          <p>
            A welcoming space where people meet, exchange ideas, and rediscover the social side of food.
          </p>
        </article>

        <article class="buurderij-gallery-card">
          <img src="<?= h($buurderijGallery3) ?>" alt="Sustainable local culture">
          <h4>Sustainable local culture</h4>
          <p>
            Buurderij reflects Haarlem’s creative, sustainable, and community-focused identity in everyday life.
          </p>
        </article>
      </section>

      <section class="buurderij-bottom-grid">
  <div class="buurderij-contribute-card">
    <h3>Contribution (pay as you like)</h3>
    <div class="buurderij-place-name">Kweekcafé</div>

    <div class="buurderij-age-box">
      <div class="buurderij-age-top">
        <span class="buurderij-age-icon">!</span>
        <span class="buurderij-age-text">Age requirement</span>
        <span class="buurderij-age-badge">16+</span>
      </div>
      <p>
        This session contains spoken-word content and is recommended for ages 16 and up.
      </p>
    </div>

    <div class="buurderij-contribute-copy">
      Choose the amount you want to contribute.<br>
      Your contribution supports the storytellers and future events.<br>
      €0 is also welcome - just reserve
    </div>

    <div class="buurderij-amount-label">Suggested amounts</div>

    <div class="buurderij-amount-grid">
      <button type="button">€0</button>
      <button type="button">€5</button>
      <button type="button">€10</button>
      <button type="button">€15</button>
      <button type="button">€20</button>
    </div>

    <div class="buurderij-custom-label">Or enter a custom amount</div>

    <div class="buurderij-custom-input-wrap">
      <input type="text" value="€ 0" placeholder="€ 0">
    </div>

    <div class="buurderij-total-row">
      <span>Your contribution</span>
      <strong>€0.00</strong>
    </div>

    <a href="/tickets" class="buurderij-reserve-btn">Reserve ›</a>
  </div>

  <div class="buurderij-info-card buurderij-info-card--design">
    <div class="buurderij-info-top">
      <div class="buurderij-info-block">
        <strong>Venue</strong>
        <span><?= h($venueText ?: 'Kweekcafe, Haarlem') ?></span>
      </div>

      <div class="buurderij-info-block">
        <strong>Select<br>the Day:</strong>
        <div class="buurderij-day-tabs buurderij-day-tabs-large">
          <span class="active">Thursday ›</span>
          <span>Friday ›</span>
        </div>
      </div>

      <div class="buurderij-info-block">
        <strong>Time</strong>
        <span>20:30 – 21:45</span>
      </div>
    </div>

    <div class="buurderij-reservation-box buurderij-reservation-box-large">
      <h4>Reservation</h4>
      <ul>
        <li>Reservation is required</li>
        <li>Even for Pay As You Like events</li>
        <li>Reservation guarantees entry (limited capacity)</li>
        <li>Exclusive lounge access</li>
        <li>Complimentary drinks</li>
        <li>Meet & greet opportunities</li>
      </ul>
    </div>
  </div>
</section>
      <section class="buurderij-last-grid">
        <div class="buurderij-last-image">
          <img src="<?= h($buurderijMain2) ?>" alt="Buurderij community team">
        </div>

        <div class="buurderij-last-text">
          <h3>Why This Story Matters</h3>
          <p>
            The Story of Buurderij Haarlem turns a local initiative into a bigger reflection on belonging, responsibility,
            and the way cities can be shaped by everyday choices. It reminds visitors that stories are not only told on stage —
            they are also lived in neighborhoods, markets, and shared community spaces.
          </p>
          <p>
            By connecting food, people, and place, Buurderij becomes a living example of how culture and community can grow
            together in Haarlem.
          </p>
        </div>
      </section>
    </section>
  </section>
  <?php endif; ?>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>