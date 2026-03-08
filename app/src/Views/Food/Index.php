<?php

$app = (new \App\Repositories\SettingsRepository())->getAll();
$foodConfig = (new \App\Repositories\FoodSettingsRepository())->getAll();

// Settings from seeder/config
$heroImage = '/images/food/' . rawurlencode($foodConfig['hero_image'] ?? 'food-hero.jpg');
$pageTitle = $viewModel->pageTitle ?? 'Food';

$introHeading = $foodConfig['intro_heading'] ?? 'Taste the Festival Spirit in Haarlem';
$introText = $foodConfig['intro_text'] ?? '';

$filters = $foodConfig['filter_labels'] ?? ['All'];
$restaurants = $viewModel->restaurants ?? [];
$localsReviews = $foodConfig['locals_reviews'] ?? [];

/**
 * Helpers
 */
$normalize = static function(string $s): string {
    $s = mb_strtolower(trim($s));
    $s = preg_replace('/\s+/', ' ', $s);
    return $s;
};

$matchesFilter = static function(array $restaurant, string $filter, callable $normalize): bool {
    if ($filter === 'All') return true;

    $filterN = $normalize($filter);
    $tags = $restaurant['tags'] ?? [];

    foreach ($tags as $t) {
        $tagN = $normalize((string)$t);

        // exact OR partial match (so "Seafood" matches "Fish & Seafood")
        if ($tagN === $filterN || str_contains($tagN, $filterN) || str_contains($filterN, $tagN)) {
            return true;
        }
    }
    return false;
};  

$renderStars = static function(float $rating): string {
    // rating expected 0..5, display as full stars only (like the screenshot style)
    $full = (int) round($rating);
    $full = max(0, min(5, $full));
    $out = '';
    for ($i = 1; $i <= 5; $i++) $out .= ($i <= $full) ? '★' : '☆';
    return $out;
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version']) ?>">
<link rel="stylesheet" href="/css/food.css?v=<?= htmlspecialchars($app['css_version']) ?>">
</head>

<body class="food-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- Hero -->
    <section class="food-hero"
             style="background-image: linear-gradient(120deg, rgba(0,0,0,0.35), rgba(0,0,0,0.65)), url('<?= htmlspecialchars($heroImage) ?>');">
        <div class="food-hero-content container">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
        </div>
    </section>

    <!-- Intro -->
    <section class="food-intro container">
    <nav class="breadcrumbs food-breadcrumbs">
    <a href="/">HOME</a>
    <span class="breadcrumb-sep">›</span>
    <span class="breadcrumb-current">FOOD</span>
    </nav>

        <h2 class="food-intro-heading"><?= htmlspecialchars($introHeading) ?></h2>
        <p class="food-intro-text"><?= nl2br(htmlspecialchars($introText)) ?></p>
    </section>

    <!-- Filter + Restaurants -->
    <section class="food-restaurants container">
        <div class="food-filter-row">
            <p class="food-filter-label">Filter by:</p>
            <div class="food-filter-pills" role="tablist" aria-label="Cuisine filters">
                <?php foreach ($filters as $i => $label):
                    $active = ($i === 0) ? 'active' : '';
                ?>
                    <button type="button"
                            class="food-filter-btn <?= $active ?>"
                            data-filter="<?= htmlspecialchars($label) ?>"
                            aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>">
                        <?= htmlspecialchars($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="food-cards-grid" id="foodCards">
    <?php foreach ($restaurants as $r):
        /** @var \App\Models\Restaurant $r */

        $id = (int)$r->restaurantId;
        $name = (string)$r->name;
        $image = (string)($r->image ?? '');
        $imagePath = $image ? '/images/food/' . rawurlencode($image) : '';

        // tags: we’ll derive from "type" because DB column is type (comma separated)
        $tags = array_filter(array_map('trim', explode(',', (string)$r->type)));
        $rating = (float)$r->stars; // your DB uses stars (3/4). keep renderStars().

        $price = '€' . number_format((float)$r->priceAdult, 2);
        $kidsPrice = 'Kids<' . (int)$r->kidAgeMax . ' €' . number_format((float)$r->priceKid, 2);

        $seats = (int)$r->seats;
        $firstSession = substr((string)$r->firstSession, 0, 5);

        $walk = $r->walkMinutesToPatronaat !== null
            ? ((int)$r->walkMinutesToPatronaat . ' min')
            : '';

        $address = (string)$r->address;

        $dataTags = array_map($normalize, array_map('strval', $tags));
        $dataTagsAttr = htmlspecialchars(json_encode($dataTags, JSON_UNESCAPED_UNICODE));
    ?>
        <a class="food-card-link"
           href="/food/restaurant/<?= $id ?>"
           aria-label="Open <?= htmlspecialchars($name) ?> details">
            <article class="food-card"
                     data-tags="<?= $dataTagsAttr ?>"
                     data-name="<?= htmlspecialchars($name) ?>">

                <div class="food-card-image-wrap">
                    <?php if ($imagePath): ?>
                        <img src="<?= htmlspecialchars($imagePath) ?>"
                             alt="<?= htmlspecialchars($name) ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <?php endif; ?>
                    <div class="food-card-placeholder" style="<?= $imagePath ? 'display:none;' : 'display:flex;' ?>">🍽️</div>
                </div>

                <div class="food-card-body">
                    <h3 class="food-card-title"><?= htmlspecialchars($name) ?></h3>

                    <p class="food-card-tags">
                        <?= htmlspecialchars(implode(', ', $tags)) ?>
                    </p>

                    <p class="food-card-stars" aria-label="Rating <?= htmlspecialchars((string)$rating) ?> out of 5">
                        <span class="food-stars"><?= htmlspecialchars($renderStars($rating)) ?></span>
                    </p>

                    <p class="food-card-prices">
                        <span><?= htmlspecialchars($price) ?></span>
                        <span class="food-price-sep">•</span>
                        <span><?= htmlspecialchars($kidsPrice) ?></span>
                    </p>

                    <p class="food-card-seats">
                        Seats <?= htmlspecialchars((string)$seats) ?>
                    </p>

                    <div class="food-card-cta">
                        <span class="food-session-badge">First session <?= htmlspecialchars($firstSession) ?></span>
                    </div>

                    <div class="food-card-footer">
                        <?php if ($walk): ?>
                            <span class="food-walk">
                                <?= htmlspecialchars($walk) ?> to the Patronaat <span aria-hidden="true">🚶</span>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($address): ?>
                        <p class="food-card-address"><?= htmlspecialchars($address) ?></p>
                    <?php endif; ?>
                </div>
            </article>
        </a>
    <?php endforeach; ?>
</div>
    </section>

    <!-- Locals reviews -->
    <section class="food-reviews">
        <div class="container">
            <h2 class="food-reviews-title">locals reviews</h2>

            <div class="food-reviews-grid">
                <?php foreach ($localsReviews as $rev):
                    $reviewer = (string)($rev['reviewer'] ?? 'Local');
                    $restaurant = (string)($rev['restaurant'] ?? '');
                    $rating = (float)($rev['rating'] ?? 0);
                    $text = (string)($rev['text'] ?? '');
                    // optional avatar field if you add it later in config
                    $avatar = $rev['avatar'] ?? null;
                ?>
                    <article class="food-review-card">
                        <div class="food-review-head">
                            <div class="food-review-avatar">
                                <?php if ($avatar): ?>
                                    <img src="<?= htmlspecialchars((string)$avatar) ?>" alt="<?= htmlspecialchars($reviewer) ?>">
                                <?php else: ?>
                                    <span aria-hidden="true">👤</span>
                                <?php endif; ?>
                            </div>
                            <div class="food-review-meta">
                                <p class="food-review-name"><?= htmlspecialchars($reviewer) ?></p>
                                <p class="food-review-restaurant"><?= htmlspecialchars($restaurant) ?></p>
                                <p class="food-review-stars"><?= htmlspecialchars($renderStars($rating)) ?></p>
                            </div>
                        </div>

                        <div class="food-review-body">
                            <p><?= nl2br(htmlspecialchars($text)) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<script>
(function () {
  const buttons = document.querySelectorAll('.food-filter-btn');
  const grid = document.getElementById('foodCards');

  // IMPORTANT: grid items are the LINKS, not the articles
  const items = Array.from(grid.querySelectorAll('.food-card-link'));

  function normalize(s) {
    return (s || '').toString().trim().toLowerCase().replace(/\s+/g, ' ');
  }

  function hasTag(cardEl, tag) {
    try {
      const tags = JSON.parse(cardEl.getAttribute('data-tags') || '[]');
      const needle = normalize(tag);
      if (needle === 'all') return true;

      return tags.some(t => {
        t = normalize(t);
        return t === needle || t.includes(needle) || needle.includes(t);
      });
    } catch (e) {
      return true;
    }
  }

  function applyFilter(filterLabel) {
    items.forEach(linkEl => {
      const cardEl = linkEl.querySelector('.food-card');
      const match = cardEl ? hasTag(cardEl, filterLabel) : true;

      // Hide/show the GRID ITEM so the grid reflows
      linkEl.style.display = match ? '' : 'none';
    });

    buttons.forEach(b => {
      b.classList.remove('active');
      b.setAttribute('aria-pressed', 'false');
    });

    const active = Array.from(buttons).find(b =>
      normalize(b.getAttribute('data-filter')) === normalize(filterLabel)
    );
    if (active) {
      active.classList.add('active');
      active.setAttribute('aria-pressed', 'true');
    }
  }

  buttons.forEach(btn => {
    btn.addEventListener('click', function () {
      applyFilter(this.getAttribute('data-filter'));
    });
  });

  if (buttons.length) applyFilter(buttons[0].getAttribute('data-filter'));
})();
</script>
</body>
</html>
