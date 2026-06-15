<?php
// app/src/Views/Food/BookingSuccess.php
// $input, $restaurant, $successMessage, $festivalDates passed from controller

// Settings for the page title, styles, body class
$pageTitle = 'Booking confirmed — ' . ($restaurant->name ?? 'Restaurant') . ' — Haarlem Festival';
$pageStyles = ['/css/pages/food.css'];
$bodyClass = 'food-booking-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Food', 'url' => '/food'],
        ['label' => $restaurant->name ?? 'Restaurant', 'url' => '/food/restaurant/' . (int)$restaurant->restaurantId],
        ['label' => 'Booking confirmed', 'url' => null],
];
?>
<!doctype html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">
<?php require __DIR__ . '/../partials/header.php'; ?>

<!-- Breadcrumbs nav -->
<?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

<main class="booking-card">

    <div class="success-icon">✓</div>

    <h1 class="section-title section-title--accent booking-title">Booking Confirmed</h1>

    <div class="booking-meta">
        <p class="section-subtitle booking-restaurant"><?= htmlspecialchars($restaurant->name) ?></p>
    </div>

    <div class="overview-block">

        <div class="overview-row">
            <span class="copy-text overview-label">Reservation ID</span>
            <span class="copy-text copy-text--sm overview-value"><?= htmlspecialchars($successMessage) ?></span>
        </div>

        <div class="overview-row">
            <span class="copy-text overview-label">Name</span>
            <span class="copy-text copy-text--sm overview-value"><?= htmlspecialchars($input['first_name'] . ' ' . $input['last_name']) ?></span>
        </div>

        <div class="overview-row">
            <span class="copy-text overview-label">Email</span>
            <span class="copy-text copy-text--sm overview-value"><?= htmlspecialchars($input['email']) ?></span>
        </div>

        <div class="overview-row">
            <span class="copy-text overview-label">Date</span>
            <span class="copy-text copy-text--sm overview-value"><?= htmlspecialchars($festivalDates[$input['booking_date']] ?? $input['booking_date']) ?></span>
        </div>

        <div class="overview-row">
            <span class="copy-text overview-label">Time</span>
            <span class="copy-text copy-text--sm overview-value"><?= htmlspecialchars(substr($input['session_time'], 0, 5)) ?></span>
        </div>

        <div class="overview-row">
            <span class="copy-text overview-label">Guests</span>
            <span class="copy-text copy-text--sm overview-value"><?= (int)$input['adults'] ?> adults · <?= (int)$input['children'] ?> children</span>
        </div>

    </div>

    <div class="form-actions">
        <!-- Opens the cart drawer -->
        <button
                class="btn btn--primary booking-btn"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#cartOffcanvas"
                aria-controls="cartOffcanvas"
        >
            View Cart &amp; Checkout
        </button>

        <a class="btn btn--light booking-btn" href="/food">Back to Restaurants</a>
        <a class="btn btn--light booking-btn" href="/">Back to Home</a>
    </div>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<?php if (!empty($_SESSION['open_cart_drawer'])): ?>
    <?php unset($_SESSION['open_cart_drawer']); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var offcanvasEl = document.getElementById('cartOffcanvas');
                if (offcanvasEl && window.bootstrap) {
                    bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
                }
            }, 300);
        });
    </script>
<?php endif; ?>

</body>
</html>