<?php
/**
 * /admin/food/settings  — GET shows form, POST saves
 *
 * Expected variables from controller:
 * @var array       $app          Site settings (site_name, css_version, …)
 * @var string      $csrf         CSRF token for 'admin_food_settings'
 * @var array       $settings     Current food_settings rows (key => value, already decoded)
 * @var string|null $success      Flash success message
 * @var string|null $error        Flash error message
 */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

/* ---- helpers to pull typed values from $settings ---- */
$str  = fn(string $k, string $def = '') => isset($settings[$k]) && is_string($settings[$k]) ? $settings[$k] : $def;
$flt  = fn(string $k, float  $def = 0.0) => isset($settings[$k]) ? (float) $settings[$k] : $def;

/*
 * festival_dates is stored as JSON array of {value, label} objects.
 * Convert to one "value|label" pair per line for the textarea.
 */
$festivalDatesRaw = $settings['festival_dates'] ?? [];
$festivalDatesLines = '';
if (is_array($festivalDatesRaw)) {
    $lines = [];
    foreach ($festivalDatesRaw as $entry) {
        if (is_array($entry) && isset($entry['value'], $entry['label'])) {
            $lines[] = $entry['value'] . '|' . $entry['label'];
        }
    }
    $festivalDatesLines = implode("\n", $lines);
}

/*
 * filter_labels is stored as JSON array of strings.
 * Convert to one label per line for the textarea.
 */
$filterLabelsRaw = $settings['filter_labels'] ?? [];
$filterLabelsLines = '';
if (is_array($filterLabelsRaw)) {
    $filterLabelsLines = implode("\n", array_map('strval', $filterLabelsRaw));
}

/*
 * locals_reviews is stored as JSON array of objects.
 * For simplicity, show the raw JSON in a textarea so admins can edit it directly.
 */
$localsReviewsJson = '';
if (is_array($settings['locals_reviews'] ?? null)) {
    $localsReviewsJson = json_encode($settings['locals_reviews'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} elseif (isset($settings['locals_reviews'])) {
    $localsReviewsJson = (string) $settings['locals_reviews'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Food settings — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <span>Settings</span>
        </nav>

        <h1 class="admin-title">Food settings</h1>
        <p class="admin-lead">
            Values are stored in <code>food_settings</code>. Missing keys fall back to defaults in
            <code>Config/food.php</code> (if present).
            <a href="/food" target="_blank" rel="noopener">View food page ↗</a>
        </p>

        <?php require __DIR__ . '/partials/admin_nav.php'; ?>

        <?php if (!empty($success)): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/food/settings" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">

            <!-- ── Hero & intro ─────────────────────────────────── -->
            <fieldset class="admin-fieldset">
                <legend>Hero &amp; intro</legend>

                <div class="admin-field">
                    <label for="hero_image">Hero image filename</label>
                    <input type="text" id="hero_image" name="hero_image"
                           value="<?= $h($str('hero_image', 'food-hero.jpg')) ?>"
                           class="admin-input" required maxlength="255">
                    <small class="admin-hint">Filename only, under <code>public/images/food/</code> (e.g. <code>food-hero.jpg</code>).</small>
                </div>

                <div class="admin-field">
                    <label for="intro_heading">Intro heading</label>
                    <input type="text" id="intro_heading" name="intro_heading"
                           value="<?= $h($str('intro_heading', 'Taste the Festival Spirit in Haarlem')) ?>"
                           class="admin-input" maxlength="255">
                </div>

                <div class="admin-field">
                    <label for="intro_text">Intro body text</label>
                    <textarea id="intro_text" name="intro_text"
                              class="admin-input admin-textarea" rows="4"><?= $h($str('intro_text')) ?></textarea>
                    <small class="admin-hint">Plain text; line breaks are preserved.</small>
                </div>
            </fieldset>

            <!-- ── Reservation fee ──────────────────────────────── -->
            <fieldset class="admin-fieldset">
                <legend>Reservation fee</legend>

                <div class="admin-field">
                    <label for="reservation_fee_per_person">Fee per person (€)</label>
                    <input type="number" id="reservation_fee_per_person" name="reservation_fee_per_person"
                           value="<?= $h((string) $flt('reservation_fee_per_person', 10.0)) ?>"
                           class="admin-input" min="0" step="0.01" required style="max-width:12rem;">
                    <small class="admin-hint">
                        Charged per guest at booking time and deducted at the restaurant.
                    </small>
                </div>
            </fieldset>

            <!-- ── Festival dates ────────────────────────────────── -->
            <fieldset class="admin-fieldset">
                <legend>Festival dates (booking calendar)</legend>
                <p class="admin-hint" style="margin-bottom:.75rem;">
                    One entry per line: <code>value|Label shown to user</code><br>
                    Example: <code>07-28|Thursday 28th July</code>
                </p>
                <div class="admin-field">
                    <label for="festival_dates_lines">Dates</label>
                    <textarea id="festival_dates_lines" name="festival_dates_lines"
                              class="admin-input admin-textarea" rows="6"
                              style="font-family:monospace;font-size:.9rem;"><?= $h($festivalDatesLines) ?></textarea>
                </div>
            </fieldset>

            <!-- ── Filter labels ─────────────────────────────────── -->
            <fieldset class="admin-fieldset">
                <legend>Cuisine filter labels</legend>
                <p class="admin-hint" style="margin-bottom:.75rem;">
                    One label per line. The first entry is the default "show all" label (e.g. <code>All</code>).
                </p>
                <div class="admin-field">
                    <label for="filter_labels_lines">Labels</label>
                    <textarea id="filter_labels_lines" name="filter_labels_lines"
                              class="admin-input admin-textarea" rows="8"
                              style="font-family:monospace;font-size:.9rem;"><?= $h($filterLabelsLines) ?></textarea>
                </div>
            </fieldset>

            <!-- ── Locals reviews ────────────────────────────────── -->
            <fieldset class="admin-fieldset">
                <legend>Locals reviews (JSON)</legend>
                <p class="admin-hint" style="margin-bottom:.75rem;">
                    Array of objects with keys: <code>reviewer</code>, <code>restaurant</code>,
                    <code>rating</code> (0–5), <code>text</code>, <code>avatar</code> (optional URL).
                    Invalid JSON will be rejected.
                </p>
                <div class="admin-field">
                    <label for="locals_reviews_json">JSON</label>
                    <textarea id="locals_reviews_json" name="locals_reviews_json"
                              class="admin-input admin-textarea" rows="10"
                              style="font-family:monospace;font-size:.85rem;"><?= $h($localsReviewsJson) ?></textarea>
                </div>
            </fieldset>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Save settings</button>
                <a href="/admin/food" class="admin-btn admin-btn-secondary">Back</a>
            </div>
        </form>

    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>