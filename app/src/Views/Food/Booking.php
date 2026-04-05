<?php
$reservationFee = (float)($foodSettings['reservation_fee_per_person'] ?? 10);
$bookUrl = '/food/restaurant/' . (int)$restaurant->restaurantId . '/booking';
// $festivalDates is passed from the controller as ['07-28' => 'Thursday 28th July', ...]
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book a table at <?= htmlspecialchars($restaurant->name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Lato:wght@300;400&display=swap">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="food-booking-page">
<?php require __DIR__ . '/../partials/header.php'; ?>

    <!-- BREADCRUMB -->
    <div class="breadcrumb-bar">
        <div class="container">
            <nav class="breadcrumbs">
                <a href="/">HOME</a>
                <span class="breadcrumb-sep">›</span>
                <a href="/food">FOOD</a>
                <span class="breadcrumb-sep">›</span>
                <a href="/food/restaurant/<?= $restaurant->restaurantId ?>"><?= htmlspecialchars($restaurant->name) ?></a>
                <span class="breadcrumb-sep">›</span>
                <span class="breadcrumb-current">Booking</span>
            </nav>
        </div>
    </div>
<main class="booking-card">

    <h1 class="booking-title">Book Your Table</h1>

    <div class="booking-meta">
        <p class="booking-restaurant"><?= htmlspecialchars($restaurant->name) ?></p>
        <p class="booking-fee">
            Adults €<?= number_format($restaurant->priceAdult, 2) ?>
            Children €<?= number_format($restaurant->priceKid, 2) ?>
        </p>
        <p class="booking-fee booking-fee--reservation">
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
            <button class="btn btn--primary" type="submit">Review Booking</button>
            <a class="btn btn--secondary" href="/food/restaurant/<?= (int)$restaurant->restaurantId ?>">Back to Restaurant</a>
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
    .booking-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.75rem;
        color: #d4922a;
        text-align: center;
        letter-spacing: 0.04em;
        margin-bottom: 8px;
    }
    .booking-meta { text-align: center; margin-bottom: 32px; }
    .booking-restaurant { font-family: 'Playfair Display', serif; font-size: 1rem; color: #e8e0d0; margin-bottom: 6px; }
    .booking-fee { font-size: 0.8rem; color: #b0a898; letter-spacing: 0.04em; margin-bottom: 2px; }
    .booking-fee--reservation { color: #8a8078; font-size: 0.75rem; }

    .alert { border-radius: 4px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.875rem; }
    .alert-success { background: #1a3a1a; border: 1px solid #3a7a3a; color: #8fd98f; }
    .alert-danger  { background: #3a1a1a; border: 1px solid #7a3a3a; color: #e08080; }
    .alert ul { padding-left: 18px; }

    .form-section { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; }
    .form-section.full { grid-template-columns: 1fr; }
    .mixed-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; align-items: start; }
    .form-group { margin-bottom: 24px; }
    .form-group label {
        display: block; font-size: 0.75rem; font-weight: 400;
        letter-spacing: 0.08em; text-transform: uppercase; color: #e8e0d0; margin-bottom: 8px;
    }
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="tel"],
    .form-group select,
    .form-group textarea {
        width: 100%; background: #c8c4bc; border: none; border-radius: 3px;
        padding: 10px 12px; font-family: 'Lato', sans-serif; font-size: 0.875rem;
        color: #3a3530; outline: none; appearance: none; -webkit-appearance: none;
    }
    .form-group input::placeholder, .form-group textarea::placeholder { color: #9a958e; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { background: #d8d4cc; }

    .select-wrapper { position: relative; }
    .select-wrapper::after {
        content: '↓'; position: absolute; right: 12px; top: 50%;
        transform: translateY(-50%); color: #5a5550; pointer-events: none;
    }
    .select-wrapper select { padding-right: 32px; cursor: pointer; }
    .form-group textarea { min-height: 140px; resize: vertical; }

    .stepper-group { margin-bottom: 24px; }
    .stepper-label {
        font-size: 0.75rem; font-weight: 400; letter-spacing: 0.08em;
        text-transform: uppercase; color: #e8e0d0; margin-bottom: 8px;
        display: flex; align-items: baseline; gap: 8px;
    }
    .stepper-label .price { font-size: 0.85rem; color: #b0a898; text-transform: none; letter-spacing: 0; }
    .stepper { display: flex; align-items: center; }
    .stepper button {
        width: 38px; height: 38px; border: none; cursor: pointer;
        font-size: 1.1rem; font-weight: 700; display: flex;
        align-items: center; justify-content: center; flex-shrink: 0;
    }
    .stepper button.minus { background: #6b1a2a; color: #f0d0d8; border-radius: 3px 0 0 3px; }
    .stepper button.plus  { background: #2a2a2a; color: #e8e0d0; border-radius: 0 3px 3px 0; }
    .stepper .stepper-val {
        flex: 1; text-align: center; background: #c8c4bc; height: 38px;
        line-height: 38px; font-family: 'Lato', sans-serif; font-size: 0.95rem; color: #3a3530;
    }

    .form-actions { margin-top: 32px; display: flex; flex-direction: column; gap: 12px; }
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