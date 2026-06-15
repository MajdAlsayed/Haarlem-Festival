<?php
$overviewUrl = '/food/restaurant/' . (int)$restaurant->restaurantId . '/booking/overview';
$backUrl     = '/food/restaurant/' . (int)$restaurant->restaurantId . '/booking';
// $festivalDates, $feePerPerson, $totalGuests, $reservationFee passed from controller

// Settings for the page title, styles, body class
$pageTitle = 'Review your booking — ' . ($restaurant->name ?? 'Restaurant') . ' — Haarlem Festival';
$pageStyles = ['/css/pages/food.css'];
$bodyClass = 'food-booking-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Food', 'url' => '/food'],
        ['label' => $restaurant->name ?? 'Restaurant', 'url' => '/food/restaurant/' . (int)$restaurant->restaurantId],
        ['label' => 'Booking', 'url' => $backUrl],
        ['label' => 'Overview', 'url' => null],
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

    <h1 class="section-title section-title--accent booking-title">Review Your Booking</h1>

    <div class="booking-meta">
        <p class="section-subtitle booking-restaurant"><?= htmlspecialchars($restaurant->name) ?></p>
    </div>

    <div class="overview-block">

        <div class="overview-row">
            <span class="copy-text overview-label">Name</span>
            <span class="copy-text copy-text--sm overview-value"><?= htmlspecialchars($input['first_name'] . ' ' . $input['last_name']) ?></span>
        </div>

        <div class="overview-row">
            <span class="copy-text overview-label">Email</span>
            <span class="copy-text copy-text--sm overview-value"><?= htmlspecialchars($input['email']) ?></span>
        </div>

        <?php if (!empty($input['phone'])): ?>
        <div class="overview-row">
            <span class="copy-text overview-label">Phone</span>
            <span class="copy-text copy-text--sm overview-value"><?= htmlspecialchars($input['phone']) ?></span>
        </div>
        <?php endif; ?>

        <div class="overview-row">
            <span class="copy-text overview-label">Date</span>
            <span class="copy-text copy-text--sm overview-value"><?= htmlspecialchars($festivalDates[$input['booking_date']] ?? $input['booking_date']) ?></span>
        </div>

        <div class="overview-row">
            <span class="copy-text overview-label">Time</span>
            <span class="copy-text copy-text--sm overview-value"><?= htmlspecialchars(substr($input['session_time'], 0, 5)) ?></span>
        </div>

        <div class="overview-row">
            <span class="copy-text overview-label">Adults</span>
            <span class="copy-text copy-text--sm overview-value"><?= (int)$input['adults'] ?></span>
        </div>

        <div class="overview-row">
            <span class="copy-text overview-label">Children</span>
            <span class="copy-text copy-text--sm overview-value"><?= (int)$input['children'] ?></span>
        </div>

        <?php if (!empty($input['special_request'])): ?>
        <div class="overview-row">
            <span class="copy-text overview-label">Special Request</span>
            <span class="copy-text copy-text--sm overview-value"><?= htmlspecialchars($input['special_request']) ?></span>
        </div>
        <?php endif; ?>

        <div class="overview-divider"></div>

        <div class="overview-row overview-row--total">
            <span class="copy-text overview-label">Reservation fee due</span>
            <span class="copy-text overview-value">€<?= number_format($reservationFee, 2) ?> (<?= $totalGuests ?> × €<?= number_format($feePerPerson, 2) ?>)</span>
        </div>

    </div>

    <form method="post" action="<?= htmlspecialchars($overviewUrl) ?>">
        <input type="hidden" name="_csrf"           value="<?= htmlspecialchars(\App\Core\Csrf::token('food_booking_confirm')) ?>">
        <input type="hidden" name="confirm_booking" value="1">

        <div class="form-actions">
            <button class="btn btn--primary booking-btn" type="submit">Add to Cart</button>
            <a class="btn btn--light booking-btn" href="<?= htmlspecialchars($backUrl) ?>">Edit Details</a>
        </div>
    </form>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>