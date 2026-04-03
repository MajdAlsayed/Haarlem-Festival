<?php
/** @var array $app */
/** @var array<string,mixed> $config */
/** @var string $csrf */
/** @var ?string $success */
/** @var ?string $error */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$lines = static function (array $arr): string {
    $arr = array_map(static fn ($x) => (string) $x, $arr);

    return implode("\n", $arr);
};
$mapLines = static function (array $m): string {
    $out = [];
    foreach ($m as $k => $v) {
        $out[] = (string) $k . '|' . (string) $v;
    }

    return implode("\n", $out);
};
$artistPages = is_array($config['artist_pages'] ?? null) ? $config['artist_pages'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jazz layout — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <a href="/admin/jazz">Jazz</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Layout &amp; images</span>
        </nav>

        <h1 class="admin-title">Jazz layout &amp; artist pages</h1>

        <?php if (!empty($success)): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/jazz/settings" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">

            <div class="admin-grid-2">
                <div class="admin-field">
                    <label for="hero_image">Homepage hero image (filename in <code>/images/jazz/</code>)</label>
                    <input type="text" id="hero_image" name="hero_image" value="<?= $h((string) ($config['hero_image'] ?? '')) ?>" class="admin-input" required>
                </div>
                <div class="admin-field">
                    <label for="placeholder_card">Placeholder card image</label>
                    <input type="text" id="placeholder_card" name="placeholder_card" value="<?= $h((string) ($config['placeholder_card'] ?? '')) ?>" class="admin-input" required>
                </div>
            </div>

            <fieldset class="admin-fieldset">
                <legend>Artist detail pages</legend>
                <?php foreach ($artistPages as $slug => $meta):
                    $meta = is_array($meta) ? $meta : [];
                    $slugKey = preg_replace('/[^a-z0-9\-]/', '', (string) $slug) ?: (string) $slug;
                    ?>
                    <h3 class="admin-subheading"><?= $h($slugKey) ?></h3>
                    <div class="admin-field">
                        <label for="ap_title_<?= $h($slugKey) ?>">Title (must match event titles in DB for linking)</label>
                        <input type="text" id="ap_title_<?= $h($slugKey) ?>" name="ap_title_<?= $h($slugKey) ?>"
                               value="<?= $h((string) ($meta['title'] ?? '')) ?>" class="admin-input" required>
                    </div>
                    <div class="admin-field">
                        <label for="ap_tagline_<?= $h($slugKey) ?>">Tagline</label>
                        <input type="text" id="ap_tagline_<?= $h($slugKey) ?>" name="ap_tagline_<?= $h($slugKey) ?>"
                               value="<?= $h((string) ($meta['tagline'] ?? '')) ?>" class="admin-input">
                    </div>
                    <div class="admin-field">
                        <label for="ap_hero_<?= $h($slugKey) ?>">Hero image filename</label>
                        <input type="text" id="ap_hero_<?= $h($slugKey) ?>" name="ap_hero_<?= $h($slugKey) ?>"
                               value="<?= $h((string) ($meta['hero_image'] ?? '')) ?>" class="admin-input" required>
                    </div>
                <?php endforeach; ?>
            </fieldset>

            <div class="admin-field">
                <label for="event_card_images">Event card images</label>
                <small class="admin-hint">One per line: <code>Exact event title|filename-in-jazz-folder.png</code>. Leave unchanged to keep current map (clearing is not supported here).</small>
                <textarea id="event_card_images" name="event_card_images" class="admin-input admin-textarea" rows="12"><?= $h($mapLines(is_array($config['event_card_images'] ?? null) ? $config['event_card_images'] : [])) ?></textarea>
            </div>

            <div class="admin-grid-2">
                <div class="admin-field">
                    <label>Thursday order</label>
                    <textarea name="thursday_order" class="admin-input admin-textarea" rows="8"><?= $h($lines(is_array($config['thursday_order'] ?? null) ? $config['thursday_order'] : [])) ?></textarea>
                </div>
                <div class="admin-field">
                    <label>Friday order</label>
                    <textarea name="friday_order" class="admin-input admin-textarea" rows="8"><?= $h($lines(is_array($config['friday_order'] ?? null) ? $config['friday_order'] : [])) ?></textarea>
                </div>
                <div class="admin-field">
                    <label>Saturday order</label>
                    <textarea name="saturday_order" class="admin-input admin-textarea" rows="8"><?= $h($lines(is_array($config['saturday_order'] ?? null) ? $config['saturday_order'] : [])) ?></textarea>
                </div>
                <div class="admin-field">
                    <label>Sunday order</label>
                    <textarea name="sunday_order" class="admin-input admin-textarea" rows="8"><?= $h($lines(is_array($config['sunday_order'] ?? null) ? $config['sunday_order'] : [])) ?></textarea>
                </div>
            </div>

            <div class="admin-field">
                <label for="all_events_order">All events grid order</label>
                <textarea id="all_events_order" name="all_events_order" class="admin-input admin-textarea" rows="10"><?= $h($lines(is_array($config['all_events_order'] ?? null) ? $config['all_events_order'] : [])) ?></textarea>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Save</button>
                <a href="/admin/jazz" class="admin-btn admin-btn-secondary">Back</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
