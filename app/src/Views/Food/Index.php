<?php

$foodConfig = $viewModel->foodSettings;
$restaurants = $viewModel->restaurants;

// Settings for the page title, styles, body class
$pageTitle = 'Food — Haarlem Festival';
$pageStyles = ['/css/pages/food.css'];
$bodyClass = 'food-page';

// Hero settings
$heroModifier = 'festival-hero--food';
$heroImage = '/images/food/' . rawurlencode($foodConfig['hero_image'] ?? 'food-hero.jpg');
$heroImageAlt = 'Food';
$heroTitle = 'Taste the Festival Spirit in Haarlem';
$heroSubtitle = '';
$heroButtonText = '';
$heroButtonUrl = '';
$heroButtonClass = 'btn btn--light';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Food', 'url' => null],
];

$introHeading = $foodConfig['intro_heading'] ?? 'Taste the Festival Spirit in Haarlem';
$introText    = $foodConfig['intro_text'] ?? '';
$filters      = $foodConfig['filter_labels'] ?? ['All'];
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
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- Hero -->
    <?php require __DIR__ . '/../partials/festival-hero.php'; ?>

    <!-- Breadcrumbs nav -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <!-- Intro -->
    <section class="food-intro container">
        <p class="copy-text food-intro-text"><?= nl2br(htmlspecialchars($introText)) ?></p>
    </section>

    <!-- Filter + Restaurants -->
    <section class="food-restaurants container">
        <div class="food-filter-row">
            <div class="filter-tabs food-filter-pills" role="tablist" aria-label="Cuisine filters">
                <?php foreach ($filters as $i => $label):
                    $active = ($i === 0) ? 'active' : '';
                ?>
                    <button type="button"
                            class="filter-tab food-filter-btn <?= $active ?>"
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
            $tags      = array_filter(array_map('trim', explode(',', $restaurant->type)));
            $imagePath = $restaurant->image ? '/images/food/' . rawurlencode($restaurant->image) : '';
            $dataTags  = htmlspecialchars(json_encode(array_map($normalize, $tags), JSON_UNESCAPED_UNICODE));
            ?>
            <a class="food-card-link"
               href="/food/restaurant/<?= (int) $restaurant->restaurantId ?>"
               aria-label="Open <?= htmlspecialchars($restaurant->name) ?> details">
                <article class="festival-card festival-card--food-restaurant food-card"
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
                        <h3 class="section-subtitle food-card-title"><?= htmlspecialchars($restaurant->name) ?></h3>
                        <p class="copy-text copy-text--sm food-card-tags"><?= htmlspecialchars(implode(', ', $tags)) ?></p>
                        <p class="food-card-stars"><?= htmlspecialchars($renderStars($restaurant->stars)) ?></p>
                        <p class="copy-text copy-text--sm food-card-prices">
                            €<?= number_format($restaurant->priceAdult, 2) ?>
                            •
                            Kids&lt;<?= (int) $restaurant->kidAgeMax ?> €<?= number_format($restaurant->priceKid, 2) ?>
                        </p>
                        <p class="copy-text copy-text--sm food-card-seats">Seats <?= (int) $restaurant->seats ?></p>
                        <p class="copy-text copy-text--sm food-card-address"><?= htmlspecialchars($restaurant->address) ?></p>
                    </div>
                </article>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Locals reviews -->
    <section class="food-reviews">
        <div class="container">
            <h2 class="section-title section-title--accent section-title--underlined food-reviews-title">Locals reviews</h2>

            <div class="food-reviews-grid">
                <?php foreach ($localsReviews as $rev):
                    $reviewer   = (string)($rev['reviewer'] ?? 'Local');
                    $restaurant = (string)($rev['restaurant'] ?? '');
                    $rating     = (float)($rev['rating'] ?? 0);
                    $text       = (string)($rev['text'] ?? '');
                    $avatar     = $rev['avatar'] ?? null;
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
                                <p class="section-subtitle food-review-name"><?= htmlspecialchars($reviewer) ?></p>
                                <p class="copy-text copy-text--sm text-accent food-review-restaurant"><?= htmlspecialchars($restaurant) ?></p>
                                <p class="food-review-stars"><?= htmlspecialchars($renderStars($rating)) ?></p>
                            </div>
                        </div>

                        <div class="food-review-body">
                            <p class="copy-text copy-text--sm"><?= nl2br(htmlspecialchars($text)) ?></p>
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
    const grid    = document.getElementById('foodCards');
    const items   = Array.from(grid.querySelectorAll('.food-card-link'));

    function normalize(s) {
        return (s || '').toString().trim().toLowerCase().replace(/\s+/g, ' ');
    }

    function hasTag(cardEl, tag) {
        try {
            const tags   = JSON.parse(cardEl.getAttribute('data-tags') || '[]');
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
            const match  = cardEl ? hasTag(cardEl, filterLabel) : true;
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