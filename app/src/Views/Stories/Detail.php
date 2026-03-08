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

 
$omdenkenHero      = '/images/Stories/details/omdenken/hero.jpg';
$omdenkenBlock1    = '/images/Stories/details/omdenken/block-1.jpg';
$omdenkenBlock2    = '/images/Stories/details/omdenken/block-2.jpg';
$omdenkenPoster    = '/images/Stories/details/omdenken/poster.jpg';

$buurderijHero     = '/images/Stories/details/buurderij/hero.jpg';
$buurderijMain     = '/images/Stories/details/buurderij/main.jpg';
$buurderijGallery1 = '/images/Stories/details/buurderij/gallery-1.jpg';
$buurderijGallery2 = '/images/Stories/details/buurderij/gallery-2.jpg';
$buurderijGallery3 = '/images/Stories/details/buurderij/gallery-3.jpg';
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
    <!-- =========================================================
         CUSTOM DETAIL PAGE: OMDENKEN PODCAST
         ========================================================= -->
    <section class="detail-top-hero gold-section" style="background-image:url('<?= h($omdenkenHero) ?>');">
      <div class="detail-top-inner">
        <h1 class="detail-title-xl">OMDENKEN – LIVE PODCAST SESSION</h1>
        <p class="detail-subtitle">
          Changing perspectives through real stories.
          A live storytelling and podcast performance where familiar problems are viewed from a surprising new angle.
        </p>
      </div>
    </section>

    <section class="section-wrap">
      <div class="section-grid-2">
        <div class="content-card">
          <h2>What You’ll Experience</h2>
          <p>
            This live podcast session combines humor, reflection, and audience connection. Visitors listen to sharp observations,
            everyday dilemmas, and real-life examples that challenge fixed ways of thinking. The result is a lively and intelligent
            experience that feels both entertaining and meaningful.
          </p>
        </div>

        <div class="meta-panel">
          <div class="meta-list">
            <div class="meta-item"><strong>Venue</strong> <?= h($venueText) ?></div>
            <div class="meta-item"><strong>Age Group</strong> <?= h($ageText) ?></div>
            <div class="meta-item"><strong>Date</strong> <?= h($dayText) ?></div>
            <div class="meta-item"><strong>Time</strong> <?= h($timeText) ?></div>
            <?php if ($langText): ?><div class="meta-item"><strong>Language</strong> <?= h($langText) ?></div><?php endif; ?>
          </div>
        </div>
      </div>

      <div class="section-grid-2 section-space-top">
        <div class="content-card">
          <h3>Why This Story Matters</h3>
          <p>
            Omdenken invites people to rethink problems instead of only solving them. It is one of the festival’s most engaging formats
            because it turns ordinary situations into stories full of insight, humor, and perspective. Visitors leave with new ideas,
            but also with the feeling that storytelling can genuinely shift the way they look at life.
          </p>
        </div>

        <div class="image-box">
          <img src="<?= h($omdenkenBlock1) ?>" alt="Omdenken podcast feature image">
        </div>
      </div>

      <div class="section-grid-2-equal section-space-top">
        <div class="image-box">
          <img src="<?= h($omdenkenPoster) ?>" alt="Omdenken poster image">
        </div>

        <div class="image-box">
          <img src="<?= h($omdenkenBlock2) ?>" alt="Omdenken stage image">
        </div>
      </div>

      <div class="stats-row">
        <div class="stat-card">Duration<small>75 min</small></div>
        <div class="stat-card">Best For<small>Reflection & Discussion</small></div>
        <div class="stat-card">Price<small>€10.00</small></div>
      </div>

      <div class="ticket-bar">
        <div class="ticket-note">
          Note: this is a popular live session and seats can fill up quickly.<br>
          Arrive early to enjoy the venue atmosphere before the recording begins.
        </div>

        <div class="ticket-actions">
          <a class="btnx primary" href="/tickets">BUY TICKETS</a>
          <a class="btnx outline" href="/stories">BACK TO STORIES</a>
        </div>
      </div>
    </section>

  <?php elseif ($isBuurderij): ?>
    <!-- =========================================================
         CUSTOM DETAIL PAGE: THE STORY OF BUURDERIJ HAARLEM
         ========================================================= -->
    <section class="detail-top-hero gold-section" style="background-image:url('<?= h($buurderijHero) ?>');">
      <div class="detail-top-inner">
        <h1 class="detail-title-xl">The Story of Buurderij Haarlem</h1>
        <p class="detail-subtitle">
          A local story about sustainable food, community, and how Haarlem connects through shared values and everyday choices.
        </p>
      </div>
    </section>

    <section class="section-wrap">
      <div class="section-grid-2">
        <div class="content-card">
          <h2>What is “The Story of Buurderij Haarlem”?</h2>
          <p>
            This session explores how local food initiatives, neighborhood participation, and direct contact between growers and residents
            can reshape a city’s social fabric. Visitors hear how Buurderij Haarlem creates more than a marketplace: it creates trust,
            conversation, and a shared sense of responsibility around food and community.
          </p>
        </div>

        <div class="meta-panel">
          <div class="meta-list">
            <div class="meta-item"><strong>Venue</strong> <?= h($venueText) ?></div>
            <div class="meta-item"><strong>Age Group</strong> <?= h($ageText) ?></div>
            <div class="meta-item"><strong>Date</strong> <?= h($dayText) ?></div>
            <div class="meta-item"><strong>Time</strong> <?= h($timeText) ?></div>
            <?php if ($langText): ?><div class="meta-item"><strong>Language</strong> <?= h($langText) ?></div><?php endif; ?>
          </div>
        </div>
      </div>

      <div class="section-grid-2 section-space-top">
        <div class="image-box">
          <img src="<?= h($buurderijMain) ?>" alt="Buurderij Haarlem main image">
        </div>

        <div class="content-card">
          <h3>The Story Behind Buurderij Haarlem</h3>
          <p>
            Buurderij Haarlem brings producers and residents together in a way that feels personal, direct, and meaningful. This story
            highlights how small local systems can create real impact in everyday city life. It shows that storytelling is not only about
            the past, but also about the social ideas shaping Haarlem today.
          </p>
        </div>
      </div>

      <div class="gallery-3">
        <div class="gallery-item">
          <img src="<?= h($buurderijGallery1) ?>" alt="Local marketplace">
          <div class="gallery-caption">
            Local food, handmade products, and neighborhood exchange come together in one welcoming space.
          </div>
        </div>

        <div class="gallery-item">
          <img src="<?= h($buurderijGallery2) ?>" alt="Shared community space">
          <div class="gallery-caption">
            A story about connection — where growers, makers, and visitors meet face to face.
          </div>
        </div>

        <div class="gallery-item">
          <img src="<?= h($buurderijGallery3) ?>" alt="Community food activity">
          <div class="gallery-caption">
            Buurderij reflects Haarlem’s creative and sustainable side through everyday human interaction.
          </div>
        </div>
      </div>

      <div class="section-grid-2 section-space-top">
        <div class="content-card">
          <h3>Reservation</h3>
          <p>
            This story is ideal for visitors interested in sustainability, social innovation, and community life. Because the session
            has a warm and conversational format, places may fill quickly.
          </p>
        </div>

        <div class="content-card">
          <h3>Why This Story Matters</h3>
          <p>
            The Story of Buurderij Haarlem turns a local initiative into a wider reflection on belonging, responsibility, and modern urban
            life. It reminds us that stories are often hidden in ordinary places — and that local action can become cultural memory.
          </p>
        </div>
      </div>

      <div class="ticket-bar">
        <div class="ticket-note">
          This performance is especially recommended for visitors interested in local culture, sustainability,
          and community-focused storytelling.
        </div>

        <div class="ticket-actions">
          <a class="btnx primary" href="/tickets">BUY TICKETS</a>
          <a class="btnx outline" href="/stories">BACK TO STORIES</a>
        </div>
      </div>
    </section>

  <?php else: ?>
    <!-- =========================================================
         DEFAULT DETAIL PAGE
         ========================================================= -->
    <section class="detail-top-hero gold-section" style="background-image:url('<?= h($heroImg) ?>');">
      <div class="detail-top-inner">
        <h1 class="detail-title-xl"><?= h($story['name'] ?? '') ?></h1>
        <p class="detail-subtitle">
          <?= h($descText) ?>
        </p>
      </div>
    </section>

    <section class="section-wrap">
      <div class="section-grid-2">
        <div class="content-card">
          <h2>About this Story</h2>
          <p><?= nl2br(h($descText)) ?></p>
        </div>

        <div class="meta-panel">
          <div class="meta-list">
            <div class="meta-item"><strong>Day</strong> <?= h($dayText) ?></div>
            <div class="meta-item"><strong>Time</strong> <?= h($timeText) ?></div>
            <div class="meta-item"><strong>Venue</strong> <?= h($venueText) ?></div>
            <?php if ($langText): ?><div class="meta-item"><strong>Language</strong> <?= h($langText) ?></div><?php endif; ?>
            <?php if ($ageText): ?><div class="meta-item"><strong>Age</strong> <?= h($ageText) ?></div><?php endif; ?>
            <?php if ($typeText): ?><div class="meta-item"><strong>Type</strong> <?= h($typeText) ?></div><?php endif; ?>
          </div>

          <div class="ticket-bar ticket-bar-default">
            <a class="btnx primary" href="/tickets">BUY TICKETS</a>
            <a class="btnx outline" href="/stories">BACK TO STORIES</a>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <h2 class="more-title">More stories from this venue</h2>

  <div class="stories-cards-grid">
    <?php foreach ($vm->stories as $s): ?>
      <?php if ((int)($s['story_id'] ?? 0) === (int)($story['story_id'] ?? 0)) continue; ?>
      <?php
        $img2 = $s['image_path'] ?? '/images/Stories/cards/default.jpg';
        $lang2 = $s['language'] ?? '';
        $age2 = $s['age'] ?? '';
      ?>
      <article class="stories-card">
        <div class="stories-card-img" style="background-image:url('<?= h($img2) ?>')"></div>

        <div class="stories-card-body">
          <h3 class="stories-card-title"><?= h($s['story_name'] ?? $s['name'] ?? '') ?></h3>

          <div class="stories-card-meta">
            <div class="meta-row">
              <span class="meta-ico">📅</span>
              <span><?= h(ucfirst($s['event_day'] ?? '')) ?> <?= h($s['start_time'] ?? '') ?></span>
            </div>

            <div class="meta-row">
              <span class="meta-ico">📍</span>
              <span><?= h($s['venue_name'] ?? '') ?><?= !empty($s['venue_city']) ? ', ' . h($s['venue_city']) : '' ?></span>
            </div>

            <?php if ($lang2): ?>
              <div class="meta-row">
                <span class="meta-ico">🌐</span>
                <span>Lang: <?= h($lang2) ?></span>
              </div>
            <?php endif; ?>

            <?php if ($age2): ?>
              <div class="meta-row">
                <span class="meta-ico">🔞</span>
                <span>Age <?= h($age2) ?></span>
              </div>
            <?php endif; ?>
          </div>

          <p class="stories-card-desc"><?= h($s['description'] ?? '') ?></p>

          <div class="stories-card-actions">
            <a class="btnx outline" href="/stories/detail?id=<?= (int)($s['story_id'] ?? 0) ?>">MORE INFO</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>