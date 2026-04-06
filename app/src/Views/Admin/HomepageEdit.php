<?php
/** @var \App\ViewModels\AdminHomepageEditViewModel $viewModel */
$app = $viewModel->appSettings;
$c = $viewModel->cmsHome;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit homepage — <?= htmlspecialchars((string)($app['site_name'] ?? 'Haarlem Festival')) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars((string)($app['css_version'] ?? '1.0')) ?>">
    <style>
        .admin-cms-wrap { max-width: 720px; margin: 1rem auto; padding: 0 1rem; }
        .admin-cms-nav { margin: 0.75rem 0 1rem; font-size: 0.95rem; }
        .admin-cms-nav a { color: #c9a227; font-weight: 600; }
        .admin-cms-field { margin: 1rem 0; }
        .admin-cms-field label { display: block; margin-bottom: 0.35rem; font-weight: 600; }
        .admin-cms-field input[type="text"], .admin-cms-field textarea { width: 100%; max-width: 640px; padding: 0.5rem; box-sizing: border-box; }
        .admin-cms-field textarea { min-height: 4rem; }
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

    <h1>Edit homepage (CMS)</h1>
    <p>Title = <code>pages</code> (slug home). Other fields = <code>site_settings</code> (keys <code>cms_home_*</code>). TinyMCE for some fields; you can upload the about image. <a href="/">View site</a></p>

    <?php if ($viewModel->success !== null): ?>
        <div class="admin-cms-msg-ok"><?= htmlspecialchars($viewModel->success) ?></div>
    <?php endif; ?>
    <?php if ($viewModel->error !== null): ?>
        <div class="admin-cms-msg-err"><?= htmlspecialchars($viewModel->error) ?></div>
    <?php endif; ?>

    <form method="post" action="/admin/cms/homepage">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

        <div class="admin-cms-field">
            <label for="page_title">Homepage title (browser tab)</label>
            <input type="text" id="page_title" name="page_title" required maxlength="255"
                   value="<?= htmlspecialchars($viewModel->pageTitle) ?>">
        </div>

        <div class="admin-cms-section">
            <h2>Hero</h2>
            <div class="admin-cms-field">
                <label for="cms_hero_eyebrow">Eyebrow (small line above title)</label>
                <input type="text" id="cms_hero_eyebrow" name="cms[hero_eyebrow]" maxlength="500"
                       value="<?= htmlspecialchars($c['hero_eyebrow'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_hero_heading">Main heading</label>
                <input type="text" id="cms_hero_heading" name="cms[hero_heading]" required maxlength="500"
                       value="<?= htmlspecialchars($c['hero_heading'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_hero_subtitle">Subtitle</label>
                <textarea id="cms_hero_subtitle" class="cms-wysiwyg" name="cms[hero_subtitle]"><?= htmlspecialchars($c['hero_subtitle'] ?? '') ?></textarea>
            </div>
            <div class="admin-cms-field">
                <label for="cms_hero_cta_label">Button label</label>
                <input type="text" id="cms_hero_cta_label" name="cms[hero_cta_label]" required maxlength="500"
                       value="<?= htmlspecialchars($c['hero_cta_label'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_hero_cta_href">Button link</label>
                <input type="text" id="cms_hero_cta_href" name="cms[hero_cta_href]" maxlength="500"
                       value="<?= htmlspecialchars($c['hero_cta_href'] ?? '') ?>">
                <p class="admin-cms-hint">Use <code>#events</code>, <code>/path</code>, or <code>https://…</code></p>
            </div>
        </div>

        <div class="admin-cms-section">
            <h2>Welcome</h2>
            <div class="admin-cms-field">
                <label for="cms_welcome_heading">Section heading</label>
                <input type="text" id="cms_welcome_heading" name="cms[welcome_heading]" required maxlength="500"
                       value="<?= htmlspecialchars($c['welcome_heading'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_welcome_p1">Paragraph 1</label>
                <textarea id="cms_welcome_p1" class="cms-wysiwyg" name="cms[welcome_p1]" required><?= htmlspecialchars($c['welcome_p1'] ?? '') ?></textarea>
            </div>
            <div class="admin-cms-field">
                <label for="cms_welcome_p2">Paragraph 2</label>
                <textarea id="cms_welcome_p2" class="cms-wysiwyg" name="cms[welcome_p2]" required><?= htmlspecialchars($c['welcome_p2'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="admin-cms-section">
            <h2>About (home)</h2>
            <div class="admin-cms-field">
                <label for="cms_about_heading">Section heading</label>
                <input type="text" id="cms_about_heading" name="cms[about_heading]" required maxlength="500"
                       value="<?= htmlspecialchars($c['about_heading'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_about_text">Body text</label>
                <textarea id="cms_about_text" class="cms-wysiwyg" name="cms[about_text]" required><?= htmlspecialchars($c['about_text'] ?? '') ?></textarea>
            </div>
            <div class="admin-cms-field">
                <label for="cms_about_image_src">Image URL path</label>
                <input type="text" id="cms_about_image_src" name="cms[about_image_src]" maxlength="500"
                       value="<?= htmlspecialchars($c['about_image_src'] ?? '') ?>">
                <p class="admin-cms-hint">Site path, e.g. <code>/images/cms/home/…</code> or upload below.</p>
            </div>
            <div class="admin-cms-upload">
                <strong>Upload about image</strong> (max 5 MB) → <code>/images/cms/home/</code>
                <div style="margin-top:0.5rem;">
                    <input type="file" id="cms-upload-home-file" accept="image/jpeg,image/png,image/gif,image/webp">
                    <button type="button" class="btn btn-primary" id="cms-upload-home-btn" style="margin-left:0.5rem;">Upload &amp; fill path</button>
                </div>
            </div>
            <div class="admin-cms-field">
                <label for="cms_about_image_alt">Image alt text</label>
                <input type="text" id="cms_about_image_alt" name="cms[about_image_alt]" required maxlength="500"
                       value="<?= htmlspecialchars($c['about_image_alt'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_about_more_label">“More info” link label</label>
                <input type="text" id="cms_about_more_label" name="cms[about_more_label]" required maxlength="500"
                       value="<?= htmlspecialchars($c['about_more_label'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_about_more_href">“More info” link URL</label>
                <input type="text" id="cms_about_more_href" name="cms[about_more_href]" maxlength="500"
                       value="<?= htmlspecialchars($c['about_more_href'] ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save homepage</button>
    </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script src="/js/admin-cms-editors.js?v=1"></script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
