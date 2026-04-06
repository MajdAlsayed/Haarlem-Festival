<?php
// app/src/Views/Food/BookingSuccess.php
// $input, $restaurant, $successMessage, $festivalDates passed from controller
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmed – <?= htmlspecialchars($restaurant->name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Lato:wght@300;400&display=swap">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="food-booking-page">
<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="booking-card">

    <div class="success-icon">✓</div>
    <h1 class="booking-title">Booking Confirmed</h1>

    <div class="booking-meta">
        <p class="booking-restaurant"><?= htmlspecialchars($restaurant->name) ?></p>
    </div>

    <div class="overview-block">
        <div class="overview-row">
            <span class="overview-label">Reservation ID</span>
            <span class="overview-value"><?= htmlspecialchars($successMessage) ?></span>
        </div>
        <div class="overview-row">
            <span class="overview-label">Name</span>
            <span class="overview-value"><?= htmlspecialchars($input['first_name'] . ' ' . $input['last_name']) ?></span>
        </div>
        <div class="overview-row">
            <span class="overview-label">Email</span>
            <span class="overview-value"><?= htmlspecialchars($input['email']) ?></span>
        </div>
        <div class="overview-row">
            <span class="overview-label">Date</span>
            <span class="overview-value"><?= htmlspecialchars($festivalDates[$input['booking_date']] ?? $input['booking_date']) ?></span>
        </div>
        <div class="overview-row">
            <span class="overview-label">Time</span>
            <span class="overview-value"><?= htmlspecialchars(substr($input['session_time'], 0, 5)) ?></span>
        </div>
        <div class="overview-row">
            <span class="overview-label">Guests</span>
            <span class="overview-value"><?= (int)$input['adults'] ?> adults · <?= (int)$input['children'] ?> children</span>
        </div>
    </div>

    <div class="form-actions">
        <!-- Opens the cart drawer -->
        <button
            class="btn btn--primary"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#cartOffcanvas"
            aria-controls="cartOffcanvas"
        >
            View Cart &amp; Checkout
        </button>
        <a class="btn btn--secondary" href="/food">Back to Restaurants</a>
        <a class="btn btn--secondary" href="/">Back to Home</a>
    </div>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<?php if (!empty($_SESSION['open_cart_drawer'])): ?>
<?php unset($_SESSION['open_cart_drawer']); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Give Bootstrap a moment to initialise
    setTimeout(function () {
        var offcanvasEl = document.getElementById('cartOffcanvas');
        if (offcanvasEl && window.bootstrap) {
            bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
        }
    }, 300);
});
</script>
<?php endif; ?>

<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body.food-booking-page {
        background: radial-gradient(ellipse at 50% 0%, #1a0e00 0%, #000000 70%);
        min-height: 100vh;
        font-family: 'Lato', sans-serif;
        font-weight: 300;
        color: #e8e0d0;
    }
    .booking-card { max-width: 420px; margin: 0 auto; padding: 40px 24px 60px; }
    .success-icon { text-align: center; font-size: 2.5rem; color: #d4922a; margin-bottom: 12px; }
    .booking-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.75rem; color: #d4922a;
        text-align: center; letter-spacing: 0.04em; margin-bottom: 8px;
    }
    .booking-meta { text-align: center; margin-bottom: 32px; }
    .booking-restaurant { font-family: 'Playfair Display', serif; font-size: 1rem; color: #e8e0d0; }

    .overview-block {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 4px; padding: 8px 0; margin-bottom: 32px;
    }
    .overview-row {
        display: flex; justify-content: space-between; align-items: baseline;
        padding: 11px 20px; border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .overview-row:last-child { border-bottom: none; }
    .overview-label { font-size: 0.75rem; letter-spacing: 0.08em; text-transform: uppercase; color: #8a8078; }
    .overview-value { font-size: 0.9rem; color: #e8e0d0; text-align: right; max-width: 65%; }

    .form-actions { display: flex; flex-direction: column; gap: 12px; }
    .btn {
        display: block; width: 100%; padding: 13px; border: none; border-radius: 3px;
        font-family: 'Lato', sans-serif; font-size: 0.875rem; letter-spacing: 0.1em;
        text-transform: uppercase; text-align: center; text-decoration: none; cursor: pointer;
    }
    .btn--primary  { background: #d4922a; color: #000; }
    .btn--secondary { background: #2a2a2a; color: #c8c4bc; }
</style>
</body>
</html>