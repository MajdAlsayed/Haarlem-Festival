<?php
$reservationFee = $foodSettings['reservation_fee_per_person'] ?? 10;

// Hero image (same filename as the card)
$heroImage = $restaurant->image ? '/images/food/' . rawurlencode($restaurant->image) : null;

// Extra photos: /public/images/food/<slug>1.jpg … <slug>5.jpg
$extraPhotos = [];
$publicDir   = dirname(__DIR__, 3) . '/public'; // .../app/public
for ($i = 1; $i <= 5; $i++) {
    $rel = "/images/food/{$restaurant->slug}{$i}.jpg";
    $abs = $publicDir . $rel;
    if (file_exists($abs)) {
        $extraPhotos[] = $rel;
    }
}

// Stars renderer (0–5)
$stars   = max(0, min(5, (int)$restaurant->stars));
$starHtml = str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);

// Map embed
$mapQ   = urlencode($restaurant->address);
$mapSrc = "https://www.google.com/maps?q={$mapQ}&output=embed";

// Settings for the page title, styles, body class
$pageTitle = ($restaurant->name ?? 'Restaurant') . ' — Haarlem Festival';
$pageStyles = ['/css/pages/food.css'];
$bodyClass = 'food-detail-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Food', 'url' => '/food'],
        ['label' => $restaurant->name ?? 'Restaurant', 'url' => null],
];
?>
<!doctype html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= htmlspecialchars($bodyClass) ?>">
<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

    <!-- HERO -->
    <section class="restaurant-hero">
        <?php if ($heroImage): ?>
            <img class="restaurant-hero__img"
                 src="<?= htmlspecialchars($heroImage) ?>"
                 alt="<?= htmlspecialchars($restaurant->name) ?>">
        <?php endif; ?>

        <div class="restaurant-hero__overlay"></div>

        <div class="restaurant-hero__content container">
            <div class="restaurant-hero__grid">
                <div>
                    <h1 class="page-hero__title restaurant-title"><?= htmlspecialchars($restaurant->name) ?></h1>

                    <div class="restaurant-stars" aria-label="<?= $stars ?> out of 5 stars">
                        <?= htmlspecialchars($starHtml) ?>
                    </div>

                    <p class="page-hero__subtitle restaurant-type"><?= htmlspecialchars($restaurant->type) ?></p>

                    <div class="restaurant-pill">
                        First session <?= htmlspecialchars(substr($restaurant->firstSession, 0, 5)) ?>
                    </div>
                </div>

                <div class="restaurant-cta">
                    <a class="btn btn--light"
                       href="/food/restaurant/<?= (int)$restaurant->restaurantId ?>/booking">
                        Book now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Breadcrumbs nav -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <div class="container">

        <!-- ABOUT -->
        <section class="restaurant-section">
            <h2 class="section-title section-title--accent section-title--underlined">About</h2>
            <p class="copy-text restaurant-about">
                Welcome to <?= htmlspecialchars($restaurant->name) ?>, where gastronomy becomes an art and hospitality
                is at the heart of our experience. Located in Haarlem, this restaurant offers a memorable dining
                experience with carefully crafted dishes and a warm atmosphere.
            </p>
        </section>

        <!-- PHOTOS -->
        <section class="restaurant-section">
            <h2 class="section-title section-title--accent section-title--underlined">Photos</h2>
            <p class="section-lead">Explore Restaurant photos</p>

            <?php if (count($extraPhotos) > 0): ?>
                <div class="restaurant-photos">
                    <?php foreach ($extraPhotos as $idx => $p): ?>
                        <img class="restaurant-photo"
                             src="<?= htmlspecialchars($p) ?>"
                             alt="<?= htmlspecialchars($restaurant->name) ?> photo <?= $idx + 1 ?>"
                             loading="lazy">
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="copy-text copy-text--muted">Photos will be added soon.</p>
            <?php endif; ?>
        </section>

        <!-- LOCATION -->
        <section class="restaurant-section">
            <h2 class="section-title section-title--accent section-title--underlined">Location</h2>

            <div class="restaurant-location">
                <div class="restaurant-location__info">
                    <p class="section-lead restaurant-address"><?= htmlspecialchars($restaurant->address) ?></p>
                </div>
                <div class="restaurant-map">
                    <iframe src="<?= htmlspecialchars($mapSrc) ?>"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Map for <?= htmlspecialchars($restaurant->name) ?>">
                    </iframe>
                </div>
            </div>
        </section>

        <section class="restaurant-section">
            <div class="reservation-note">
                <h2 class="section-subtitle">Reservation is mandatory.</h2>
                <p class="copy-text">
                    A reservation fee of €<?= htmlspecialchars((string)$reservationFee) ?> per person will be charged
                    when booking on the Haarlem Festival site. This fee will be deducted from the final check at the restaurant.
                </p>
            </div>
        </section>

        <!-- Reserve Now button under location -->
        <section class="restaurant-section">
            <div class="location-booking-btn location-booking-btn--center">
                <a class="btn btn--light"
                   href="/food/restaurant/<?= (int)$restaurant->restaurantId ?>/booking">
                    Book now
                </a>
            </div>
        </section>

        <!-- REVIEWS -->
        <div class="restaurant-divider"></div>

        <h2 class="section-title section-title--accent section-title--underlined">Reviews</h2>

        <div class="restaurant-reviews">
            <article class="review-card">
                <div class="review-header">
                    <div class="review-avatar"></div>
                    <div>
                        <div class="section-subtitle review-name">Jo aisen</div>
                        <div class="copy-text copy-text--sm text-accent review-sub"><?= htmlspecialchars($restaurant->name) ?></div>
                        <div class="review-stars">★★★★★</div>
                    </div>
                </div>
                <p class="copy-text review-text">
                    Amazing restaurant and a very memorable dining experience.
                    Every bite was perfectly balanced and beautifully presented.
                </p>
            </article>

            <article class="review-card">
                <div class="review-header">
                    <div class="review-avatar"></div>
                    <div>
                        <div class="section-subtitle review-name">Tolga Goktaş</div>
                        <div class="copy-text copy-text--sm text-accent review-sub"><?= htmlspecialchars($restaurant->name) ?></div>
                        <div class="review-stars">★★★★★</div>
                    </div>
                </div>
                <p class="copy-text review-text">
                    Exceptional experience from start to finish. Thoughtfully prepared courses,
                    fresh ingredients, and just-right portions.
                </p>
            </article>
        </div>

        <div class="restaurant-divider"></div>
    </div>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>