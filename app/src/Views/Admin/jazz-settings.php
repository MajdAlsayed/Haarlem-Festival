<?php
/**
 * Big JSON-style editor for jazz “chrome”: hero image, card photos, artist page text, sort orders — saved to DB on top of defaults.
 * AdminJazzController::settings().
 */
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
        <p class="admin-hint admin-hint--block">You can <strong>upload</strong> homepage hero, placeholder card, and artist hero images (JPG/PNG/WebP/GIF, max ~10 MB each). Uploads are saved under <code>/images/jazz/uploads/layout/</code> and override the matching filename field for that save. Event card image mappings are still edited as text below.</p>

        <?php if (!empty($success)): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/jazz/settings" enctype="multipart/form-data" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">

            <div class="admin-grid-2">
                <div class="admin-field">
                    <label for="hero_image_upload">Upload homepage hero</label>
                    <input type="file" id="hero_image_upload" name="hero_image_upload" class="admin-input" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif">
                    <label for="hero_image" class="admin-label-spaced">Or filename in <code>/images/jazz/</code></label>
                    <input type="text" id="hero_image" name="hero_image" value="<?= $h((string) ($config['hero_image'] ?? '')) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="placeholder_card_upload">Upload placeholder card</label>
                    <input type="file" id="placeholder_card_upload" name="placeholder_card_upload" class="admin-input" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif">
                    <label for="placeholder_card" class="admin-label-spaced">Or filename</label>
                    <input type="text" id="placeholder_card" name="placeholder_card" value="<?= $h((string) ($config['placeholder_card'] ?? '')) ?>" class="admin-input">
                </div>
            </div>

            <fieldset class="admin-fieldset">
                <legend>Artist detail pages</legend>
                <p class="admin-hint admin-hint--block" style="margin-bottom:1rem;">
                    <strong>Schedule tables</strong> on artist pages (dates, times, venues, prices) come from
                    <a href="/admin/jazz/events">Jazz → Events</a>: each row uses the event <strong>title</strong> matching the artist title below.
                    Edit the event there to change what appears in the schedule.
                </p>
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
                        <label for="ap_hero_upload_<?= $h($slugKey) ?>">Upload hero image</label>
                        <input type="file" id="ap_hero_upload_<?= $h($slugKey) ?>" name="ap_hero_upload_<?= $h($slugKey) ?>" class="admin-input" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif">
                        <label for="ap_hero_<?= $h($slugKey) ?>" class="admin-label-spaced">Or hero filename</label>
                        <input type="text" id="ap_hero_<?= $h($slugKey) ?>" name="ap_hero_<?= $h($slugKey) ?>"
                               value="<?= $h((string) ($meta['hero_image'] ?? '')) ?>" class="admin-input">
                    </div>
                    <div class="admin-field">
                        <label for="ap_intro_<?= $h($slugKey) ?>">Introduction (under hero)</label>
                        <small class="admin-hint">Plain text. Line breaks are kept. Leave empty to use the first matching event’s description from Events, or the page’s built-in default text.</small>
                        <textarea id="ap_intro_<?= $h($slugKey) ?>" name="ap_intro_<?= $h($slugKey) ?>" class="admin-input admin-textarea" rows="5"><?= $h((string) ($meta['intro_text'] ?? '')) ?></textarea>
                    </div>
                    <div class="admin-field">
                        <label for="ap_highlights_plain_<?= $h($slugKey) ?>">Career highlights</label>
                        <small class="admin-hint admin-hint--block">
                            Plain text only. <strong>New paragraph:</strong> leave one blank line between blocks.
                            <strong>Orange emphasis:</strong> wrap words in <code>[[accent]]like this[[/accent]]</code> (no HTML tags needed).
                            Single line breaks inside a paragraph are kept as line breaks on the site.
                            Leave empty to use the built-in default story on the public page (unless you use Advanced HTML below).
                        </small>
                        <textarea id="ap_highlights_plain_<?= $h($slugKey) ?>" name="ap_highlights_plain_<?= $h($slugKey) ?>" class="admin-input admin-textarea" rows="12"><?= $h((string) ($meta['career_highlights_plain'] ?? '')) ?></textarea>
                    </div>
                    <details class="admin-field admin-details-advanced">
                        <summary>Advanced: raw HTML (optional)</summary>
                        <small class="admin-hint admin-hint--block">
                            Only used if the box above is <strong>empty</strong>. For experts who need links or extra markup.
                            If you fill both, the simple text box wins and this HTML is cleared on save.
                        </small>
                        <textarea id="ap_highlights_<?= $h($slugKey) ?>" name="ap_highlights_<?= $h($slugKey) ?>" class="admin-input admin-textarea" rows="8"><?= $h((string) ($meta['career_highlights_html'] ?? '')) ?></textarea>
                    </details>
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
