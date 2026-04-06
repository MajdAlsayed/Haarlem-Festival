<?php
/** Admin form to edit Dance page content; submits to AdminDanceController::save. Uses shared admin.css like Jazz/Food. */
/** @var \App\ViewModels\AdminDanceEditViewModel $viewModel */
$app = $viewModel->appSettings;
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Dance page — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1.0') ?>">
    <link rel="stylesheet" href="/css/admin.css?v=<?= $h($app['css_version'] ?? '1.0') ?>">
</head>
<body class="admin-page" data-upload-csrf="<?= $h($viewModel->uploadCsrf) ?>" data-upload-url="/admin/cms/upload">
<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--wide">
        <?php require __DIR__ . '/partials/admin_nav.php'; ?>

        <nav class="admin-breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/dance">Dance</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Edit page</span>
        </nav>

        <h1 class="admin-title">Dance page copy</h1>
        <p class="admin-lead">Data in <code>dance_settings</code>, defaults in <code>dance.php</code>. TinyMCE on hero + about. Upload fills hero filename. <a href="/dance" target="_blank" rel="noopener">View public page</a></p>

        <?php if ($viewModel->success !== null): ?>
            <div class="admin-alert admin-alert-success"><?= $h($viewModel->success) ?></div>
        <?php endif; ?>
        <?php if ($viewModel->error !== null): ?>
            <div class="admin-alert admin-alert-error"><?= $h($viewModel->error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/cms/dance" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= $h($viewModel->csrf) ?>">

            <div class="admin-field">
                <label for="dance_page_title">Page title (browser tab + hero H1)</label>
                <input type="text" id="dance_page_title" name="dance_page_title" required maxlength="120"
                       value="<?= $h($viewModel->dancePageTitle) ?>" class="admin-input">
            </div>

            <fieldset class="admin-fieldset">
                <legend>Section headings</legend>
                <div class="admin-field">
                    <label for="about_section_heading">About block heading</label>
                    <input type="text" id="about_section_heading" name="about_section_heading" required maxlength="120"
                           value="<?= $h($viewModel->aboutSectionHeading) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="featured_section_title">Featured events title</label>
                    <input type="text" id="featured_section_title" name="featured_section_title" required maxlength="120"
                           value="<?= $h($viewModel->featuredSectionTitle) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="all_events_section_title">All events title</label>
                    <input type="text" id="all_events_section_title" name="all_events_section_title" required maxlength="120"
                           value="<?= $h($viewModel->allEventsSectionTitle) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="artists_section_title">Artists section title</label>
                    <input type="text" id="artists_section_title" name="artists_section_title" required maxlength="120"
                           value="<?= $h($viewModel->artistsSectionTitle) ?>" class="admin-input">
                </div>
            </fieldset>

            <div class="admin-field">
                <label for="hero_image">Hero background image filename</label>
                <input type="text" id="hero_image" name="hero_image" required maxlength="255"
                       value="<?= $h($viewModel->heroImage) ?>" class="admin-input">
                <small class="admin-hint">File under <code>/public/images/dance/</code> (name only), or upload below.</small>
                <div class="admin-panel" style="margin-top:0.75rem;padding:1rem;">
                    <strong>Upload hero image</strong> (max 5 MB)
                    <div style="margin-top:0.5rem;">
                        <input type="file" id="cms-upload-dance-hero-file" accept="image/jpeg,image/png,image/gif,image/webp">
                        <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" id="cms-upload-dance-hero-btn" style="margin-left:0.5rem;">Upload &amp; fill filename</button>
                    </div>
                </div>
            </div>

            <div class="admin-field">
                <label for="hero_subtitle">Hero subtitle</label>
                <textarea id="hero_subtitle" class="cms-wysiwyg admin-input admin-textarea" name="hero_subtitle" required rows="4"><?= $h($viewModel->heroSubtitle) ?></textarea>
            </div>

            <div class="admin-field">
                <label for="hero_cta_label">Hero button label</label>
                <input type="text" id="hero_cta_label" name="hero_cta_label" required maxlength="120"
                       value="<?= $h($viewModel->heroCtaLabel) ?>" class="admin-input">
            </div>

            <fieldset class="admin-fieldset">
                <legend>About Dance — paragraphs</legend>
                <div class="admin-field">
                    <label for="about_p1">Paragraph 1 (rich text)</label>
                    <textarea id="about_p1" class="cms-wysiwyg admin-input admin-textarea" name="about_p1" rows="5"><?= $h($viewModel->aboutP1) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="about_p2">Paragraph 2</label>
                    <textarea id="about_p2" class="cms-wysiwyg admin-input admin-textarea" name="about_p2" rows="4"><?= $h($viewModel->aboutP2) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="about_p3">Paragraph 3 (rich text)</label>
                    <textarea id="about_p3" class="cms-wysiwyg admin-input admin-textarea" name="about_p3" rows="5"><?= $h($viewModel->aboutP3) ?></textarea>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset">
                <legend>Card images (one filename per line)</legend>
                <p class="admin-hint">Filenames only, as in <code>/public/images/dance/</code>. Leave a block empty to keep config defaults on save.</p>
                <div class="admin-field">
                    <label for="featured_images_lines">Featured row (order matches featured events)</label>
                    <textarea id="featured_images_lines" class="admin-input admin-textarea" name="featured_images_lines" maxlength="8000" style="font-family:monospace;font-size:0.9rem;min-height:7rem;"><?= $h($viewModel->featuredImagesLines) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="friday_images_lines">Friday tab</label>
                    <textarea id="friday_images_lines" class="admin-input admin-textarea" name="friday_images_lines" maxlength="8000" style="font-family:monospace;font-size:0.9rem;min-height:7rem;"><?= $h($viewModel->fridayImagesLines) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="saturday_images_lines">Saturday tab</label>
                    <textarea id="saturday_images_lines" class="admin-input admin-textarea" name="saturday_images_lines" maxlength="8000" style="font-family:monospace;font-size:0.9rem;min-height:7rem;"><?= $h($viewModel->saturdayImagesLines) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="sunday_images_lines">Sunday tab</label>
                    <textarea id="sunday_images_lines" class="admin-input admin-textarea" name="sunday_images_lines" maxlength="8000" style="font-family:monospace;font-size:0.9rem;min-height:7rem;"><?= $h($viewModel->sundayImagesLines) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="featured_genre_labels_lines">Featured genre pills (one per line, order matches cards)</label>
                    <textarea id="featured_genre_labels_lines" class="admin-input admin-textarea" name="featured_genre_labels_lines" maxlength="500" style="font-family:monospace;font-size:0.9rem;min-height:4rem;"><?= $h($viewModel->featuredGenreLabelsLines) ?></textarea>
                </div>
            </fieldset>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Save Dance page</button>
                <a href="/admin/dance" class="admin-btn admin-btn-secondary">Dance CMS hub</a>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script src="/js/admin-cms-editors.js?v=1"></script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
