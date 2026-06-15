<?php
$reservationFee = (float)($foodSettings['reservation_fee_per_person'] ?? 10);
$bookUrl = '/food/restaurant/' . (int)$restaurant->restaurantId . '/booking';
// $festivalDates is passed from the controller as ['07-28' => 'Thursday 28th July', ...]


// Settings for the page title, styles, body class
$pageTitle = 'Book a table at ' . ($restaurant->name ?? 'Restaurant') . ' — Haarlem Festival';
$pageStyles = ['/css/pages/food.css'];
$bodyClass = 'food-booking-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Food', 'url' => '/food'],
        ['label' => $restaurant->name ?? 'Restaurant', 'url' => '/food/restaurant/' . (int)$restaurant->restaurantId],
        ['label' => 'Booking', 'url' => null],
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

    <h1 class="section-title section-title--accent booking-title">Book Your Table</h1>

    <div class="booking-meta">
        <p class="section-subtitle booking-restaurant"><?= htmlspecialchars($restaurant->name) ?></p>
        <p class="copy-text copy-text--sm booking-fee">
            Adults €<?= number_format($restaurant->priceAdult, 2) ?>
            Children €<?= number_format($restaurant->priceKid, 2) ?>
        </p>
        <p class="copy-text copy-text--sm copy-text--muted booking-fee booking-fee--reservation">
            + €<?= number_format($reservationFee, 2) ?> reservation fee per person
        </p>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success" role="status"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars($bookUrl) ?>" novalidate>
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token('food_booking')) ?>">

        <!-- First Name / Last Name -->
        <div class="form-section">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input id="first_name" name="first_name" type="text"
                       placeholder="First Name"
                       value="<?= htmlspecialchars($input['first_name'] ?: ($_COOKIE['user_first_name'] ?? '')) ?>"
                       required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input id="last_name" name="last_name" type="text"
                       placeholder="Last Name"
                       value="<?= htmlspecialchars($input['last_name'] ?: ($_COOKIE['user_last_name'] ?? '')) ?>"
                       required>
            </div>
        </div>

        <!-- Email -->
        <div class="form-section full">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input id="email" name="email" type="email"
                       placeholder="Email Address"
                       value="<?= htmlspecialchars($input['email'] ?: ($_COOKIE['user_email'] ?? '')) ?>"
                       required>
            </div>
        </div>

        <!-- Time + Adults -->
        <div class="mixed-row">
            <div class="form-group">
                <label for="session_time">Time</label>
                <div class="select-wrapper">
                    <select id="session_time" name="session_time" required>
                        <option value="">Select a time</option>
                        <option value="<?= $restaurant->firstSession ?>"
                            <?= ($input['session_time'] === $restaurant->firstSession) ? 'selected' : '' ?>>
                            <?= substr($restaurant->firstSession, 0, 5) ?>
                        </option>
                        <?php if ($restaurant->sessions >= 2): ?>
                        <option value="<?= $restaurant->secondSession ?>"
                            <?= ($input['session_time'] === $restaurant->secondSession) ? 'selected' : '' ?>>
                            <?= substr($restaurant->secondSession, 0, 5) ?>
                        </option>
                        <?php endif; ?>
                        <?php if ($restaurant->sessions >= 3): ?>
                        <option value="<?= $restaurant->thirdSession ?>"
                            <?= ($input['session_time'] === $restaurant->thirdSession) ? 'selected' : '' ?>>
                            <?= substr($restaurant->thirdSession, 0, 5) ?>
                        </option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="stepper-group">
                <div class="stepper-label">
                    Adults <span class="price">€<?= number_format($restaurant->priceAdult, 2) ?></span>
                </div>
                <div class="stepper">
                    <button type="button" class="minus" data-target="adults">−</button>
                    <div class="stepper-val" id="adults-display">0</div>
                    <button type="button" class="plus" data-target="adults">+</button>
                    <input type="hidden" id="adults" name="adults"
                           value="<?= (int)($input['adults'] ?? 0) ?>">
                </div>
            </div>
        </div>

        <!-- Date + Children -->
        <div class="mixed-row">
            <div class="form-group">
                <label for="booking_date">Date</label>
                <div class="select-wrapper">
                    <select id="booking_date" name="booking_date" required>
                        <option value="">Select a date</option>
                        <?php foreach ($festivalDates as $val => $label): ?>
                        <option value="<?= htmlspecialchars($val) ?>"
                            <?= ($input['booking_date'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="stepper-group">
                <div class="stepper-label">
                    Children <span class="price">€<?= number_format($restaurant->priceKid, 2) ?></span>
                </div>
                <div class="stepper">
                    <button type="button" class="minus" data-target="children">−</button>
                    <div class="stepper-val" id="children-display">0</div>
                    <button type="button" class="plus" data-target="children">+</button>
                    <input type="hidden" id="children" name="children"
                           value="<?= (int)($input['children'] ?? 0) ?>">
                </div>
            </div>
        </div>

        <!-- Special Requests -->
        <div class="form-section full">
            <div class="form-group">
                <label for="special_request">Special Requests</label>
                <textarea id="special_request" name="special_request"
                          placeholder="Any dietary requirements?"><?= htmlspecialchars($input['special_request'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Phone (hidden but submitted) -->
        <input type="hidden" name="phone" value="<?= htmlspecialchars($input['phone'] ?? '') ?>">

        <!-- Actions -->
        <div class="form-actions">
            <button class="btn btn--primary booking-btn" type="submit">Review Booking</button>
            <a class="btn btn--light booking-btn" href="/food/restaurant/<?= (int)$restaurant->restaurantId ?>">Back to Restaurant</a>
        </div>

    </form>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
(function () {
    ['adults', 'children'].forEach(function (key) {
        var inp  = document.getElementById(key);
        var disp = document.getElementById(key + '-display');
        if (inp && disp) disp.textContent = inp.value || '0';
    });
    document.querySelectorAll('.stepper button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = this.dataset.target;
            var inp    = document.getElementById(target);
            var disp   = document.getElementById(target + '-display');
            var val    = parseInt(inp.value, 10) || 0;
            if (this.classList.contains('plus'))  val = val + 1;
            if (this.classList.contains('minus')) val = Math.max(0, val - 1);
            inp.value        = val;
            disp.textContent = val;
        });
    });
}());
</script>
</body>
</html>