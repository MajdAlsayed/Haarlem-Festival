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
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($restaurant->name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="food-detail-page">
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
                    <a class="btn btn--reserve"
                       href="/food/restaurant/<?= (int)$restaurant->restaurantId ?>/booking">
                       Book now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- BREADCRUMB -->
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
            Welcome to <?= htmlspecialchars($restaurant->name) ?>, where gastronomy becomes an art and hospitality
            is at the heart of our experience. Located in Haarlem, this restaurant offers a memorable dining
            experience with carefully crafted dishes and a warm atmosphere.
        </p>
    </section>

    <!-- PHOTOS -->
    <section class="container restaurant-section">
        <h2>Explore Restaurant photos</h2>

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
            <p class="muted">Photos will be added soon.</p>
        <?php endif; ?>
    </section>

    <!-- LOCATION -->
    <section class="container restaurant-section">
        <h2>Location</h2>

        <div class="restaurant-location">
            <div class="restaurant-location__info">
                <p class="restaurant-address"><?= htmlspecialchars($restaurant->address) ?></p>
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

    <section class="container restaurant-section">
                <div class="reservation-note">
                <strong>Reservation is mandatory.</strong>
                A reservation fee of €<?= htmlspecialchars((string)$reservationFee) ?> per person will be charged
                when booking on the Haarlem Festival site. This fee will be deducted from the final check at the restaurant.
            </div></section>
    <!-- Reserve Now button under location (centered) -->
    <section class="container restaurant-section">
        <div class="location-booking-btn location-booking-btn--center">
            <a class="btn btn--reserve"
               href="/food/restaurant/<?= (int)$restaurant->restaurantId ?>/booking">
               Book now
            </a>
        </div>
    </section>

    <!-- BOOK + REVIEWS -->
        <div class="container">
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
                        Amazing restaurant and a very memorable dining experience.
                        Every bite was perfectly balanced and beautifully presented.
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
                        Exceptional experience from start to finish. Thoughtfully prepared courses,
                        fresh ingredients, and just-right portions.
                    </p>
                </article>
            </div>

            <div class="restaurant-divider"></div>
        </div>
    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>

<style>
/* ============================================================
   BUTTON CSS — paste into your style.css (or a dedicated file)
   ============================================================

   btn--reserve
   Matches the "Book your table" reference image:
   white background, dark text, large rounded pill, generous padding.
*/

.btn--reserve {
    display: inline-block;
    padding: 18px 48px;
    background-color: #ffffff;
    color: #1a1a1a;
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    text-decoration: none;
    border-radius: 50px;          /* full pill shape */
    border: none;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    white-space: nowrap;
}

.btn--reserve:hover,
.btn--reserve:focus {
    background-color: #f0f0f0;
    color: #000000;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.22);
    outline: none;
}

.btn--reserve:active {
    background-color: #e0e0e0;
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.18);
}

/* Right-align the hero CTA column so Reserve Now sits on the right */
.restaurant-cta {
    display: flex;
    justify-content: flex-end;
    align-items: center;
}

/* Center the Reserve Now button below the location section */
.location-booking-btn--center {
    display: flex;
    justify-content: center;
}
</style>