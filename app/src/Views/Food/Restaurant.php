<?php
/** @var \App\Models\Restaurant $restaurant */

$app = (new \App\Repositories\SettingsRepository())->getAll();
$foodSettings = (new \App\Repositories\FoodSettingsRepository())->getAll();

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
    <link rel="stylesheet" href="/css/food.css?v=<?= htmlspecialchars($app['css_version']) ?>">
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
            <nav class="restaurant-breadcrumbs">
                <a href="/">HOME</a>
                <span>•</span>
                <a href="/food">FOOD</a>
                <span>•</span>
                <span class="active"><?= htmlspecialchars($restaurant->name) ?></span>
            </nav>

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

    <section class="container restaurant-section">
        <h2>About</h2>
        <p class="restaurant-about">
            <!-- Optional: later store about text in DB. For now show a decent default -->
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

                <?php if (!empty($restaurant->website)): ?>
                    <p><a href="<?= htmlspecialchars($restaurant->website) ?>" target="_blank" rel="noopener">Our Website</a></p>
                <?php endif; ?>

                <?php if (!empty($restaurant->phone)): ?>
                    <p><?= htmlspecialchars($restaurant->phone) ?></p>
                <?php endif; ?>
            </div>

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
                <!-- Static examples to match screenshot.
                     Later you can store reviews in DB (restaurant_reviews table). -->
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

            <!-- Optional: Special requests field (you requested this) -->
            <form id="book-form" class="special-request">
                <label for="special_request">Special requests (allergies, wheelchair, etc.)</label>
                <textarea id="special_request" name="special_request" rows="4" placeholder="Type your request..."></textarea>
                <!-- Later: wire this to Add to Cart / Reservation -->
            </form>

        </div>
    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
<style>/* =========================================================
   RESTAURANT DETAIL PAGE
   ========================================================= */

.food-detail-page {
  background: #000;
  color: #fff;
}

/* ================= HERO ================= */

.restaurant-hero {
  position: relative;
  height: 460px;
  overflow: hidden;
}

.restaurant-hero__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transform: scale(1.02);
}

.restaurant-hero__overlay {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.85) 65%, #000 100%);
}

.restaurant-hero__content {
  position: absolute;
  bottom: 40px;
  left: 0;
  right: 0;
  z-index: 2;
}

.restaurant-breadcrumbs {
  font-size: 13px;
  opacity: .85;
  display: flex;
  gap: 10px;
  align-items: center;
  margin-bottom: 20px;
}

.restaurant-breadcrumbs a {
  color: #d89b1b;
  text-decoration: none;
}

.restaurant-breadcrumbs .active {
  color: #d89b1b;
  position: relative;
  padding-bottom: 4px;
}

.restaurant-breadcrumbs .active::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: 0;
  width: 100%;
  height: 2px;
  background: #d89b1b;
}

.restaurant-hero__grid {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 40px;
}

.restaurant-title {
  font-size: 46px;
  margin: 0 0 10px 0;
  font-weight: 700;
}

.restaurant-stars {
  font-size: 18px;
  letter-spacing: 3px;
  color: #f7c948;
  margin-bottom: 10px;
}

.restaurant-type {
  opacity: .85;
  margin-bottom: 15px;
  max-width: 500px;
}

.restaurant-pill {
  display: inline-block;
  background: #d89b1b;
  color: #000;
  font-weight: 700;
  font-size: 13px;
  padding: 6px 12px;
  border-radius: 6px;
}

.restaurant-cta {
  display: flex;
  justify-content: flex-end;
}

.btn--light {
  background: #fff;
  color: #000;
  padding: 16px 28px;
  border-radius: 6px;
  font-weight: 700;
  text-decoration: none;
  transition: all .2s ease;
}

.btn--light:hover {
  background: #e6e6e6;
  transform: translateY(-2px);
}

/* ================= SECTIONS ================= */

.restaurant-section {
  padding: 60px 0;
}

.restaurant-section h2 {
  font-size: 22px;
  margin-bottom: 24px;
}

.restaurant-about {
  max-width: 800px;
  line-height: 1.7;
  opacity: .9;
}

/* ================= PHOTOS GRID ================= */

.restaurant-photos {
  display: grid;
  grid-template-columns: 1.4fr 1fr 1fr;
  grid-auto-rows: 180px;
  gap: 16px;
}

.restaurant-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 14px;
  transition: transform .3s ease;
}

.restaurant-photo:hover {
  transform: scale(1.03);
}

.restaurant-photo:first-child {
  grid-row: span 2;
}

/* ================= LOCATION ================= */

.restaurant-location {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 30px;
}

.restaurant-address {
  font-size: 15px;
  margin-bottom: 10px;
  opacity: .85;
}

.restaurant-map iframe {
  width: 100%;
  height: 280px;
  border: 0;
  border-radius: 14px;
}

/* ================= BOTTOM SECTION ================= */

.restaurant-bottom {
  padding: 80px 0;
  background:
    radial-gradient(circle at 30% 10%, rgba(216,155,27,0.35) 0%, rgba(0,0,0,0) 60%),
    #000;
}

.restaurant-bottom__cta {
  display: flex;
  justify-content: center;
  margin-bottom: 40px;
}

.restaurant-divider {
  height: 1px;
  background: rgba(255,255,255,0.25);
  margin: 40px 0;
}

/* ================= REVIEWS ================= */

.restaurant-reviews {
  display: flex;
  flex-direction: column;
  gap: 30px;
  margin-top: 20px;
}

.review-card {
  max-width: 820px;
  padding: 22px 24px;
  border-radius: 14px;
  background: rgba(0,0,0,0.75);
  box-shadow: 0 20px 50px rgba(0,0,0,0.6);
  position: relative;
}

.review-header {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-bottom: 12px;
}

.review-avatar {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  background: rgba(255,255,255,0.2);
}

.review-name {
  font-weight: 700;
}

.review-sub {
  font-size: 13px;
  color: #d89b1b;
  margin-top: 3px;
}

.review-stars {
  letter-spacing: 3px;
  margin-top: 6px;
  color: #00c6a2;
}

.review-text {
  line-height: 1.7;
  opacity: .9;
  margin: 0;
}

/* ================= RESERVATION NOTE ================= */

.reservation-note {
  max-width: 820px;
  margin-top: 20px;
  line-height: 1.6;
  opacity: .9;
}

.special-request {
  margin-top: 20px;
  max-width: 820px;
}

.special-request textarea {
  width: 100%;
  margin-top: 10px;
  padding: 14px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.2);
  background: rgba(255,255,255,0.05);
  color: #fff;
  resize: vertical;
}

.special-request textarea:focus {
  outline: none;
  border-color: #d89b1b;
  box-shadow: 0 0 0 2px rgba(216,155,27,0.3);
}

/* ================= RESPONSIVE ================= */

@media (max-width: 900px) {

  .restaurant-hero__grid {
    flex-direction: column;
    align-items: flex-start;
  }

  .restaurant-location {
    grid-template-columns: 1fr;
  }

  .restaurant-photos {
    grid-template-columns: 1fr 1fr;
  }

  .restaurant-photo:first-child {
    grid-row: span 1;
  }

}</style>