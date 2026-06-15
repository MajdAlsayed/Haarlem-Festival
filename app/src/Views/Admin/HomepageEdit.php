<?php
/** @var \App\ViewModels\AdminHomepageEditViewModel $viewModel */
$app = $viewModel->appSettings;
$c = $viewModel->cmsHome;

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$pageTitle = 'Edit homepage — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= $h($bodyClass) ?>"
      data-upload-csrf="<?= $h($viewModel->uploadCsrf) ?>"
      data-upload-url="/admin/cms/upload">
<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--cms-form admin-cms-home">
        <nav class="admin-breadcrumb">
            <a href="/"><?= htmlspecialchars((string)($app['site_name'] ?? 'Festival')) ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Homepage CMS</span>
        </nav>

        <h1 class="admin-title">Edit homepage</h1>
        <p class="admin-lead admin-lead--cms">Title comes from <code class="admin-cms-inline-code">pages</code> (slug <code class="admin-cms-inline-code">home</code>). Other fields are <code class="admin-cms-inline-code">site_settings</code> keys <code class="admin-cms-inline-code">cms_home_*</code>. TinyMCE on selected fields; you can upload the about image. <a href="/" target="_blank" rel="noopener">View site</a></p>

    <?php if ($viewModel->success !== null): ?>
        <div class="admin-alert admin-alert-success"><?= htmlspecialchars($viewModel->success) ?></div>
    <?php endif; ?>
    <?php if ($viewModel->error !== null): ?>
        <div class="admin-alert admin-alert-error"><?= htmlspecialchars($viewModel->error) ?></div>
    <?php endif; ?>

    <form method="post" action="/admin/cms/homepage" class="admin-cms-home-form">
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
            <div class="admin-cms-upload admin-cms-upload--home">
                <strong>Upload about image</strong> (max 5 MB) → <code class="admin-cms-inline-code">/images/cms/home/</code>
                <div class="admin-cms-upload-row">
                    <input type="file" id="cms-upload-home-file" accept="image/jpeg,image/png,image/gif,image/webp">
                    <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" id="cms-upload-home-btn">Upload &amp; fill path</button>
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

        <div class="admin-cms-section">
            <h2>Events section</h2>
            <div class="admin-cms-field">
                <label for="cms_events_heading">Section heading</label>
                <input type="text" id="cms_events_heading" name="cms[events_heading]" maxlength="500"
                       value="<?= htmlspecialchars($c['events_heading'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_events_subtitle">Section subtitle</label>
                <input type="text" id="cms_events_subtitle" name="cms[events_subtitle]" maxlength="500"
                       value="<?= htmlspecialchars($c['events_subtitle'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_events_info_label">Info button label</label>
                <input type="text" id="cms_events_info_label" name="cms[events_info_label]" maxlength="500"
                       value="<?= htmlspecialchars($c['events_info_label'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_events_tickets_label">Tickets button label</label>
                <input type="text" id="cms_events_tickets_label" name="cms[events_tickets_label]" maxlength="500"
                       value="<?= htmlspecialchars($c['events_tickets_label'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_events_category_dance">Dance category display label</label>
                <input type="text" id="cms_events_category_dance" name="cms[events_category_dance]" maxlength="500"
                       value="<?= htmlspecialchars($c['events_category_dance'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_events_category_jazz">Jazz category display label</label>
                <input type="text" id="cms_events_category_jazz" name="cms[events_category_jazz]" maxlength="500"
                       value="<?= htmlspecialchars($c['events_category_jazz'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_events_category_history">History category display label</label>
                <input type="text" id="cms_events_category_history" name="cms[events_category_history]" maxlength="500"
                       value="<?= htmlspecialchars($c['events_category_history'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_events_category_yammy">Food category display label</label>
                <input type="text" id="cms_events_category_yammy" name="cms[events_category_yammy]" maxlength="500"
                       value="<?= htmlspecialchars($c['events_category_yammy'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_events_category_stories">Stories category display label</label>
                <input type="text" id="cms_events_category_stories" name="cms[events_category_stories]" maxlength="500"
                       value="<?= htmlspecialchars($c['events_category_stories'] ?? '') ?>">
            </div>
        </div>

        <div class="admin-cms-section">
            <h2>Expect section</h2>
            <div class="admin-cms-field">
                <label for="cms_expect_heading">Section heading</label>
                <input type="text" id="cms_expect_heading" name="cms[expect_heading]" maxlength="500"
                       value="<?= htmlspecialchars($c['expect_heading'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_expect_intro">Intro text</label>
                <textarea id="cms_expect_intro" name="cms[expect_intro]"><?= htmlspecialchars($c['expect_intro'] ?? '') ?></textarea>
            </div>
            <div class="admin-cms-field">
                <label for="cms_expect_subheading">Subheading</label>
                <input type="text" id="cms_expect_subheading" name="cms[expect_subheading]" maxlength="500"
                       value="<?= htmlspecialchars($c['expect_subheading'] ?? '') ?>">
            </div>
            <div class="admin-cms-field">
                <label for="cms_expect_cta">CTA text</label>
                <textarea id="cms_expect_cta" name="cms[expect_cta]"><?= htmlspecialchars($c['expect_cta'] ?? '') ?></textarea>
            </div>
            <div class="admin-cms-field">
                <label for="cms_expect_cards_json">Cards JSON (array of {icon,title,items[]})</label>
                <textarea id="cms_expect_cards_json" class="admin-cms-json-textarea" name="cms[expect_cards_json]"><?= htmlspecialchars($c['expect_cards_json'] ?? '[]') ?></textarea>
            </div>
        </div>

        <div class="admin-cms-home-actions">
            <button type="submit" class="admin-btn admin-btn-primary">Save homepage</button>
        </div>
    </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script src="/js/admin-cms-editors.js?v=1"></script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
