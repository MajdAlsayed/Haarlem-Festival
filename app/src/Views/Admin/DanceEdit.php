<?php
/** @var \App\ViewModels\AdminDanceEditViewModel $viewModel */
$app = $viewModel->appSettings;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Dance page — <?= htmlspecialchars((string)($app['site_name'] ?? 'Haarlem Festival')) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars((string)($app['css_version'] ?? '1.0')) ?>">
    <style>
        .admin-cms-wrap { max-width: 720px; margin: 1rem auto; padding: 0 1rem; }
        .admin-cms-nav { margin: 0.75rem 0 1rem; font-size: 0.95rem; }
        .admin-cms-nav a { color: #c9a227; font-weight: 600; }
        .admin-cms-field { margin: 1rem 0; }
        .admin-cms-field label { display: block; margin-bottom: 0.35rem; font-weight: 600; }
        .admin-cms-field input[type="text"], .admin-cms-field textarea { width: 100%; max-width: 640px; padding: 0.5rem; box-sizing: border-box; }
        .admin-cms-field textarea { min-height: 4rem; }
        .admin-cms-field textarea.admin-cms-lines { min-height: 7rem; font-family: monospace; font-size: 0.9rem; }
        .admin-cms-hint { font-size: 0.85rem; color: #888; margin-top: 0.25rem; }
        .admin-cms-msg-ok { background: #143; border: 1px solid #2a5; padding: 0.75rem; margin-bottom: 1rem; border-radius: 4px; }
        .admin-cms-msg-err { background: #fee; border: 1px solid #c00; padding: 0.75rem; margin-bottom: 1rem; border-radius: 4px; color: #300; }
        .admin-cms-section { margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #333; }
        .admin-cms-section h2 { font-size: 1.15rem; margin-bottom: 0.5rem; }
        .admin-cms-upload { margin: 0.5rem 0 1rem; padding: 0.75rem; background: #1a1a1a; border-radius: 6px; font-size: 0.9rem; }
        .admin-cms-upload input[type="file"] { max-width: 100%; }
    </style>
</head>
<body data-upload-csrf="<?= htmlspecialchars($viewModel->uploadCsrf) ?>" data-upload-url="/admin/cms/upload">
<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-cms-wrap">
    <?php require __DIR__ . '/partials/admin_nav.php'; ?>

    <h1>Edit Dance page (CMS)</h1>
    <p>Data in <code>dance_settings</code>, defaults in <code>dance.php</code>. TinyMCE on hero + about. Upload fills hero filename. <a href="/dance">View page</a></p>

    <?php if ($viewModel->success !== null): ?>
        <div class="admin-cms-msg-ok"><?= htmlspecialchars($viewModel->success) ?></div>
    <?php endif; ?>
    <?php if ($viewModel->error !== null): ?>
        <div class="admin-cms-msg-err"><?= htmlspecialchars($viewModel->error) ?></div>
    <?php endif; ?>

    <form method="post" action="/admin/cms/dance">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

        <div class="admin-cms-field">
            <label for="dance_page_title">Page title (browser tab + hero H1)</label>
            <input type="text" id="dance_page_title" name="dance_page_title" required maxlength="120"
                   value="<?= htmlspecialchars($viewModel->dancePageTitle) ?>">
        </div>

        <div class="admin-cms-section">
            <h2>Section headings</h2>
            <div class="admin-cms-field">
                <label for="about_section_heading">About block heading</label>
                <input type="text" id="about_section_heading" name="about_section_heading" required maxlength="120"
                       value="<?= htmlspecialchars($viewModel->aboutSectionHeading) ?>">
            </div>
            <div class="admin-cms-field">
                <label for="featured_section_title">Featured events title</label>
                <input type="text" id="featured_section_title" name="featured_section_title" required maxlength="120"
                       value="<?= htmlspecialchars($viewModel->featuredSectionTitle) ?>">
            </div>
            <div class="admin-cms-field">
                <label for="all_events_section_title">All events title</label>
                <input type="text" id="all_events_section_title" name="all_events_section_title" required maxlength="120"
                       value="<?= htmlspecialchars($viewModel->allEventsSectionTitle) ?>">
            </div>
            <div class="admin-cms-field">
                <label for="artists_section_title">Artists section title</label>
                <input type="text" id="artists_section_title" name="artists_section_title" required maxlength="120"
                       value="<?= htmlspecialchars($viewModel->artistsSectionTitle) ?>">
            </div>
        </div>

        <div class="admin-cms-field">
            <label for="hero_image">Hero background image filename</label>
            <input type="text" id="hero_image" name="hero_image" required maxlength="255"
                   value="<?= htmlspecialchars($viewModel->heroImage) ?>">
            <p class="admin-cms-hint">File under <code>/public/images/dance/</code> (name only), or upload below.</p>
            <div class="admin-cms-upload">
                <strong>Upload hero image</strong> (max 5 MB)
                <div style="margin-top:0.5rem;">
                    <input type="file" id="cms-upload-dance-hero-file" accept="image/jpeg,image/png,image/gif,image/webp">
                    <button type="button" class="btn btn-primary" id="cms-upload-dance-hero-btn" style="margin-left:0.5rem;">Upload &amp; fill filename</button>
                </div>
            </div>
        </div>

        <div class="admin-cms-field">
            <label for="hero_subtitle">Hero subtitle</label>
            <textarea id="hero_subtitle" class="cms-wysiwyg" name="hero_subtitle" required><?= htmlspecialchars($viewModel->heroSubtitle) ?></textarea>
        </div>

        <div class="admin-cms-field">
            <label for="hero_cta_label">Hero button label</label>
            <input type="text" id="hero_cta_label" name="hero_cta_label" required maxlength="120"
                   value="<?= htmlspecialchars($viewModel->heroCtaLabel) ?>">
        </div>

        <div class="admin-cms-section">
            <h2>About Dance — paragraphs</h2>
            <div class="admin-cms-field">
                <label for="about_p1">Paragraph 1 (rich text)</label>
                <textarea id="about_p1" class="cms-wysiwyg" name="about_p1"><?= htmlspecialchars($viewModel->aboutP1) ?></textarea>
            </div>
            <div class="admin-cms-field">
                <label for="about_p2">Paragraph 2</label>
                <textarea id="about_p2" class="cms-wysiwyg" name="about_p2"><?= htmlspecialchars($viewModel->aboutP2) ?></textarea>
            </div>
            <div class="admin-cms-field">
                <label for="about_p3">Paragraph 3 (rich text)</label>
                <textarea id="about_p3" class="cms-wysiwyg" name="about_p3"><?= htmlspecialchars($viewModel->aboutP3) ?></textarea>
            </div>
        </div>

        <div class="admin-cms-section">
            <h2>Card images (one filename per line)</h2>
            <p class="admin-cms-hint">Filenames only, as in <code>/public/images/dance/</code>. Leave a block empty to keep config defaults on save.</p>
            <div class="admin-cms-field">
                <label for="featured_images_lines">Featured row (order matches featured events)</label>
                <textarea id="featured_images_lines" class="admin-cms-lines" name="featured_images_lines" maxlength="8000"><?= htmlspecialchars($viewModel->featuredImagesLines) ?></textarea>
            </div>
            <div class="admin-cms-field">
                <label for="friday_images_lines">Friday tab</label>
                <textarea id="friday_images_lines" class="admin-cms-lines" name="friday_images_lines" maxlength="8000"><?= htmlspecialchars($viewModel->fridayImagesLines) ?></textarea>
            </div>
            <div class="admin-cms-field">
                <label for="saturday_images_lines">Saturday tab</label>
                <textarea id="saturday_images_lines" class="admin-cms-lines" name="saturday_images_lines" maxlength="8000"><?= htmlspecialchars($viewModel->saturdayImagesLines) ?></textarea>
            </div>
            <div class="admin-cms-field">
                <label for="sunday_images_lines">Sunday tab</label>
                <textarea id="sunday_images_lines" class="admin-cms-lines" name="sunday_images_lines" maxlength="8000"><?= htmlspecialchars($viewModel->sundayImagesLines) ?></textarea>
            </div>
            <div class="admin-cms-field">
                <label for="featured_genre_labels_lines">Featured genre pills (one per line, order matches cards)</label>
                <textarea id="featured_genre_labels_lines" class="admin-cms-lines" name="featured_genre_labels_lines" maxlength="500"><?= htmlspecialchars($viewModel->featuredGenreLabelsLines) ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Dance page</button>
    </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script src="/js/admin-cms-editors.js?v=1"></script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
