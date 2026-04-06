<?php
/**
 * /admin/food/restaurants/new  — create
 * /admin/food/restaurants/edit — edit (GET ?id=N, POST to /save)
 *
 * @var array                         $app   Site settings
 * @var \App\Models\Restaurant|null   $row   null = new, object = existing
 * @var string                        $csrf  CSRF token for 'admin_food_restaurant'
 * @var string|null                   $error Inline validation error (re-render)
 */
$h      = fn($v)            => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$val    = fn(string $field) => $row !== null ? $h((string) (property_exists($row, $field) ? $row->$field : '')) : '';
$isNew  = $row === null;
$id     = $isNew ? 0 : (int) $row->restaurantId;

// Helpers for numeric fields that might be 0 / null
$numVal = function (string $field, string $default = '') use ($row): string {
    if ($row === null) {
        return $default;
    }
    $v = property_exists($row, $field) ? $row->$field : null;
    return $v !== null ? (string) $v : $default;
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $isNew ? 'New' : 'Edit' ?> restaurant — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/admin.css?v=<?= $h($app['css_version'] ?? '1') ?>">
</head>
<body class="admin-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--wide">

        <nav class="admin-breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/food">Food</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/food/restaurants">Restaurants</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span><?= $isNew ? 'New' : 'Edit' ?></span>
        </nav>

        <h1 class="admin-title"><?= $isNew ? 'New restaurant' : 'Edit restaurant' ?></h1>

        <?php if (!empty($error)): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/food/restaurants/save" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf"          value="<?= $h($csrf) ?>">
            <input type="hidden" name="restaurant_id"  value="<?= $id ?>">

            <!-- ── Basic info ─────────────────────────────────── -->
            <fieldset class="admin-fieldset">
                <legend>Basic information</legend>

                <div class="admin-grid-2">
                    <div class="admin-field">
                        <label for="name">Name <span style="color:#e88a92">*</span></label>
                        <input type="text" id="name" name="name"
                               value="<?= $val('name') ?>"
                               class="admin-input" required maxlength="150">
                    </div>
                    <div class="admin-field">
                        <label for="slug">URL slug <span style="color:#e88a92">*</span></label>
                        <input type="text" id="slug" name="slug"
                               value="<?= $val('slug') ?>"
                               class="admin-input" required maxlength="180"
                               pattern="[a-z0-9\-]+"
                               placeholder="e.g. cafe-de-roemer">
                        <small class="admin-hint">Lowercase letters, numbers, hyphens only. Used in URLs.</small>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="address">Address <span style="color:#e88a92">*</span></label>
                    <input type="text" id="address" name="address"
                           value="<?= $val('address') ?>"
                           class="admin-input" required maxlength="255">
                </div>

                <div class="admin-grid-2">
                    <div class="admin-field">
                        <label for="type">Cuisine type(s) <span style="color:#e88a92">*</span></label>
                        <input type="text" id="type" name="type"
                               value="<?= $val('type') ?>"
                               class="admin-input" required maxlength="255"
                               placeholder="e.g. Dutch, Fish &amp; Seafood">
                        <small class="admin-hint">Comma-separated values used by the filter pills.</small>
                    </div>
                    <div class="admin-field">
                        <label for="image">Card / hero image filename</label>
                        <input type="text" id="image" name="image"
                               value="<?= $val('image') ?>"
                               class="admin-input" maxlength="255"
                               placeholder="Cafe-de-Roemer.jpg">
                        <small class="admin-hint">Filename under <code>public/images/food/</code>. Leave blank for placeholder.</small>
                    </div>
                </div>

                <div class="admin-grid-2">
                    <div class="admin-field">
                        <label for="stars">Star rating (0–5) <span style="color:#e88a92">*</span></label>
                        <input type="number" id="stars" name="stars"
                               value="<?= $h($numVal('stars', '3')) ?>"
                               class="admin-input" required min="0" max="5" step="1"
                               style="max-width:8rem;">
                    </div>
                    <div class="admin-field">
                        <label for="seats">Total seats <span style="color:#e88a92">*</span></label>
                        <input type="number" id="seats" name="seats"
                               value="<?= $h($numVal('seats', '')) ?>"
                               class="admin-input" required min="1"
                               style="max-width:10rem;">
                    </div>
                </div>
            </fieldset>

            <!-- ── Pricing ─────────────────────────────────────── -->
            <fieldset class="admin-fieldset">
                <legend>Pricing</legend>

                <div class="admin-grid-2">
                    <div class="admin-field">
                        <label for="price_adult">Price per adult (€) <span style="color:#e88a92">*</span></label>
                        <input type="number" id="price_adult" name="price_adult"
                               value="<?= $h($numVal('priceAdult', '')) ?>"
                               class="admin-input" required min="0" step="0.01"
                               style="max-width:12rem;">
                    </div>
                    <div class="admin-field">
                        <label for="price_kid">Price per child (€) <span style="color:#e88a92">*</span></label>
                        <input type="number" id="price_kid" name="price_kid"
                               value="<?= $h($numVal('priceKid', '')) ?>"
                               class="admin-input" required min="0" step="0.01"
                               style="max-width:12rem;">
                    </div>
                </div>

                <div class="admin-field">
                    <label for="kid_age_max">Child age max (≤) <span style="color:#e88a92">*</span></label>
                    <input type="number" id="kid_age_max" name="kid_age_max"
                           value="<?= $h($numVal('kidAgeMax', '12')) ?>"
                           class="admin-input" required min="1" max="17"
                           style="max-width:8rem;">
                    <small class="admin-hint">Guests up to (and including) this age pay the child price.</small>
                </div>
            </fieldset>

            <!-- ── Sessions ────────────────────────────────────── -->
            <fieldset class="admin-fieldset">
                <legend>Dining sessions</legend>
                <p class="admin-hint" style="margin-bottom:.75rem;">
                    Unused session slots are hidden in the booking form automatically.
                    Use <code>HH:MM:SS</code> format (e.g. <code>18:00:00</code>).
                </p>

                <div class="admin-grid-2">
                    <div class="admin-field">
                        <label for="sessions">Number of sessions (1–3) <span style="color:#e88a92">*</span></label>
                        <input type="number" id="sessions" name="sessions"
                               value="<?= $h($numVal('sessions', '2')) ?>"
                               class="admin-input" required min="1" max="3" step="1"
                               style="max-width:8rem;">
                    </div>
                    <div class="admin-field">
                        <label for="duration_hours">Session duration (hours) <span style="color:#e88a92">*</span></label>
                        <input type="number" id="duration_hours" name="duration_hours"
                               value="<?= $h($numVal('durationHours', '1.5')) ?>"
                               class="admin-input" required min="0.5" step="0.5"
                               style="max-width:10rem;">
                    </div>
                </div>

                <div class="admin-grid-2">
                    <div class="admin-field">
                        <label for="first_session">First session time <span style="color:#e88a92">*</span></label>
                        <input type="text" id="first_session" name="first_session"
                               value="<?= $val('firstSession') ?>"
                               class="admin-input" required maxlength="10"
                               placeholder="17:00:00">
                    </div>
                    <div class="admin-field">
                        <label for="second_session">Second session time</label>
                        <input type="text" id="second_session" name="second_session"
                               value="<?= $val('secondSession') ?>"
                               class="admin-input" maxlength="10"
                               placeholder="19:30:00">
                        <small class="admin-hint">Leave blank if only one session.</small>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="third_session">Third session time</label>
                    <input type="text" id="third_session" name="third_session"
                           value="<?= $val('thirdSession') ?>"
                           class="admin-input" maxlength="10"
                           placeholder="22:00:00" style="max-width:14rem;">
                    <small class="admin-hint">Leave blank if fewer than three sessions.</small>
                </div>
            </fieldset>

            <!-- ── Location extras ────────────────────────────── -->
            <fieldset class="admin-fieldset">
                <legend>Location extras</legend>

                <div class="admin-field">
                    <label for="walk_minutes_to_patronaat">Walk minutes to Patronaat</label>
                    <input type="number" id="walk_minutes_to_patronaat" name="walk_minutes_to_patronaat"
                           value="<?= $h($numVal('walkMinutesToPatronaat', '')) ?>"
                           class="admin-input" min="0"
                           style="max-width:10rem;">
                    <small class="admin-hint">Leave blank if not applicable.</small>
                </div>
            </fieldset>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <?= $isNew ? 'Create restaurant' : 'Save changes' ?>
                </button>
                <a href="/admin/food/restaurants" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>

    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
/* Auto-generate slug from name when slug is empty */
(function () {
    var nameEl = document.getElementById('name');
    var slugEl = document.getElementById('slug');
    if (!nameEl || !slugEl) return;
    nameEl.addEventListener('input', function () {
        if (slugEl.dataset.touched) return;
        slugEl.value = nameEl.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
    });
    slugEl.addEventListener('input', function () {
        slugEl.dataset.touched = '1';
    });
    /* Pre-mark as touched if slug already has a value (edit mode) */
    if (slugEl.value.trim() !== '') slugEl.dataset.touched = '1';
})();
</script>

</body>
</html>