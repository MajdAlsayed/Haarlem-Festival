<?php
$app = $viewModel->appSettings;
$foodConfig = $viewModel->foodSettings;
$restaurants = $viewModel->restaurants;

$pageTitle = 'Food';
$heroImage = '/images/food/' . rawurlencode($foodConfig['hero_image'] ?? 'food-hero.jpg');
$introHeading = $foodConfig['intro_heading'] ?? 'Taste the Festival Spirit in Haarlem';
$introText = $foodConfig['intro_text'] ?? '';
$filters = $foodConfig['filter_labels'] ?? ['All'];
$localsReviews = $foodConfig['locals_reviews'] ?? [];

$normalize = static function (string $value): string {
    $value = mb_strtolower(trim($value));
    return preg_replace('/\s+/', ' ', $value);
};

$renderStars = static function (int $stars): string {
    $stars = max(0, min(5, $stars));
    return str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version']) ?>">
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
    <?php foreach ($restaurants as $restaurant): ?>
    <?php
    $tags = array_filter(array_map('trim', explode(',', $restaurant->type)));
    $imagePath = $restaurant->image ? '/images/food/' . rawurlencode($restaurant->image) : '';
    $dataTags = htmlspecialchars(json_encode(array_map($normalize, $tags), JSON_UNESCAPED_UNICODE));
    ?>
    <a class="food-card-link"
       href="/food/restaurant/<?= (int) $restaurant->restaurantId ?>"
       aria-label="Open <?= htmlspecialchars($restaurant->name) ?> details">
        <article class="food-card"
                 data-tags="<?= $dataTags ?>"
                 data-name="<?= htmlspecialchars($restaurant->name) ?>">

            <div class="food-card-image-wrap">
                <?php if ($imagePath): ?>
                    <img src="<?= htmlspecialchars($imagePath) ?>"
                         alt="<?= htmlspecialchars($restaurant->name) ?>"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <?php endif; ?>
                <div class="food-card-placeholder" style="<?= $imagePath ? 'display:none;' : 'display:flex;' ?>">🍽️</div>
            </div>

            <div class="food-card-body">
                <h3 class="food-card-title"><?= htmlspecialchars($restaurant->name) ?></h3>
                <p class="food-card-tags"><?= htmlspecialchars(implode(', ', $tags)) ?></p>
                <p class="food-card-stars"><?= htmlspecialchars($renderStars($restaurant->stars)) ?></p>
                <p class="food-card-prices">
                    €<?= number_format($restaurant->priceAdult, 2) ?>
                    •
                    Kids&lt;<?= (int) $restaurant->kidAgeMax ?> €<?= number_format($restaurant->priceKid, 2) ?>
                </p>
                <p class="food-card-seats">Seats <?= (int) $restaurant->seats ?></p>
                <p class="food-card-address"><?= htmlspecialchars($restaurant->address) ?></p>
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
