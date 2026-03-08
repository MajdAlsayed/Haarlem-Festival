<?php

$app = $appSettings;
$foodConfig = $foodSettings;

$reservationFee = $foodSettings['reservation_fee_per_person'] ?? 10;

// HERO (same as card)
$heroImage = $restaurant->image ? '/images/food/' . rawurlencode($restaurant->image) : null;

// Extra photos follow: /public/images/food/<slug>1.jpg ... <slug>5.jpg
$extraPhotos = [];
$publicDir = dirname(__DIR__, 3) . '/public'; // .../app/public
for ($i = 1; $i <= 5; $i++) {
    $rel = "/images/food/{$restaurant->slug}{$i}.jpg";
    $abs = $publicDir . $rel;
    if (file_exists($abs)) {
        $extraPhotos[] = $rel;
    }
}

// Simple stars renderer (0-5)
$stars = max(0, min(5, (int)$restaurant->stars));
$starHtml = str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);

// Map embed (address only; simple)
$mapQ = urlencode($restaurant->address);
$mapSrc = "https://www.google.com/maps?q={$mapQ}&output=embed";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($restaurant->name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version']) ?>">
</head>

<body class="food-detail-page">
<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

    <!-- HERO -->
    <section class="restaurant-hero">
        <?php if ($heroImage): ?>
            <img class="restaurant-hero__img" src="<?= htmlspecialchars($heroImage) ?>" alt="<?= htmlspecialchars($restaurant->name) ?>">
        <?php endif; ?>

        <div class="restaurant-hero__overlay"></div>

        <div class="restaurant-hero__content container">
            <div class="restaurant-hero__grid">
                <div>
                    <h1 class="restaurant-title"><?= htmlspecialchars($restaurant->name) ?></h1>

                    <div class="restaurant-stars" aria-label="<?= $stars ?> out of 5 stars">
                        <?= htmlspecialchars($starHtml) ?>
                    </div>

                    <p class="restaurant-type"><?= htmlspecialchars($restaurant->type) ?></p>

                    <div class="restaurant-pill">
                        First session <?= htmlspecialchars(substr($restaurant->firstSession, 0, 5)) ?>
                    </div>
                </div>

                <div class="restaurant-cta">
                    <a class="btn btn--light" href="#book">Book your table</a>
                </div>
            </div>
        </div>
    </section>

    <!-- BREADCRUMB (below hero, matching food index style) -->
    <div class="breadcrumb-bar">
        <div class="container">
            <nav class="breadcrumbs">
                <a href="/">HOME</a>
                <span class="breadcrumb-sep">›</span>
                <a href="/food">FOOD</a>
                <span class="breadcrumb-sep">›</span>
                <span class="breadcrumb-current"><?= htmlspecialchars($restaurant->name) ?></span>
            </nav>
        </div>
    </div>

    <!-- ABOUT -->
    <section class="container restaurant-section">
        <h2>About</h2>
        <p class="restaurant-about">
            Welcome to <?= htmlspecialchars($restaurant->name) ?>, where gastronomy becomes an art and hospitality is at the heart of our experience.
            Located in Haarlem, this restaurant offers a memorable dining experience with carefully crafted dishes and a warm atmosphere.
        </p>
    </section>

    <!-- PHOTOS -->
    <section class="container restaurant-section">
        <h2>Explore Restaurant photos</h2>

        <?php if (count($extraPhotos) > 0): ?>
            <div class="restaurant-photos">
                <?php foreach ($extraPhotos as $idx => $p): ?>
                    <img
                        class="restaurant-photo"
                        src="<?= htmlspecialchars($p) ?>"
                        alt="<?= htmlspecialchars($restaurant->name) ?> photo <?= $idx + 1 ?>"
                        loading="lazy"
                    >
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="muted">Photos will be added soon.</p>
        <?php endif; ?>
    </section>

    <!-- LOCATION -->
    <section class="container restaurant-section">
        <h2>Location</h2>

        <div class="restaurant-location">
            <div class="restaurant-location__info">
                <p class="restaurant-address"><?= htmlspecialchars($restaurant->address) ?></p>
            <div class="restaurant-map">
                <iframe
                    src="<?= htmlspecialchars($mapSrc) ?>"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Map for <?= htmlspecialchars($restaurant->name) ?>"
                ></iframe>
            </div>
        </div>
    </section>

    <!-- BOOK + REVIEWS -->
    <section id="book" class="restaurant-bottom">
        <div class="container">
            <div class="restaurant-bottom__cta">
                <a class="btn btn--light" href="#book-form">Book your table</a>
            </div>

            <div class="restaurant-divider"></div>

            <h2>locals reviews</h2>

            <div class="restaurant-reviews">
                <article class="review-card">
                    <div class="review-header">
                        <div class="review-avatar"></div>
                        <div>
                            <div class="review-name">Jo aisen</div>
                            <div class="review-sub"><?= htmlspecialchars($restaurant->name) ?></div>
                            <div class="review-stars">★★★★★</div>
                        </div>
                    </div>
                    <p class="review-text">
                        Amazing restaurant and a very memorable dining experience. Every bite was perfectly balanced and beautifully presented.
                    </p>
                </article>

                <article class="review-card">
                    <div class="review-header">
                        <div class="review-avatar"></div>
                        <div>
                            <div class="review-name">Tolga Goktaş</div>
                            <div class="review-sub"><?= htmlspecialchars($restaurant->name) ?></div>
                            <div class="review-stars">★★★★★</div>
                        </div>
                    </div>
                    <p class="review-text">
                        Exceptional experience from start to finish. Thoughtfully prepared courses, fresh ingredients, and just-right portions.
                    </p>
                </article>
            </div>

            <div class="restaurant-divider"></div>

            <div class="reservation-note">
                <strong>Reservation is mandatory.</strong>
                A reservation fee of €<?= htmlspecialchars((string)$reservationFee) ?> per person will be charged when booking on the Haarlem Festival site.
                This fee will be deducted from the final check at the restaurant.
            </div>
        </div>
    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
