<?php
if (!function_exists('h')) {
    function h($s) {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

$detailPage = is_array($detailPage ?? null) ? $detailPage : [];
$highlights = is_array($highlights ?? null) ? $highlights : [];
$gallery    = is_array($gallery ?? null) ? $gallery : [];

if (empty($highlights)) {
    $highlights = [['icon' => '', 'title' => '', 'description' => '']];
}

if (empty($gallery)) {
    $gallery = [['image' => '', 'heading' => '', 'text' => '']];
}

$isEdit    = !empty($detailPage);
$pageTitle = $isEdit ? 'Edit Detail Page' : 'Add Detail Page';
$storyName = $story['name'] ?? 'Story';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> — CMS</title>
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/Stories/storiescms.css?v=2">
</head>
<body>

<main class="admin-main">
    <div class="admin-container">

        <nav class="admin-breadcrumb" aria-label="Breadcrumb">
            <a href="/admin">CMS</a>
            <span class="admin-breadcrumb-sep">/</span>
            <a href="/cms/stories">Stories</a>
            <span class="admin-breadcrumb-sep">/</span>
            <span><?= h($pageTitle) ?></span>
        </nav>

        <div class="story-cms-header">
            <div>
                <h1 class="admin-title"><?= h($pageTitle) ?></h1>
                <p class="admin-lead">
                    <?php if ($isEdit): ?>
                        Update the detail page content for this story.
                    <?php else: ?>
                        Create a custom detail page for this story.
                    <?php endif; ?>
                </p>
            </div>

            <div class="story-cms-badge">📖 <?= h($storyName) ?></div>
        </div>

        <form method="post" action="/cms/stories/detail-page/save" class="admin-form admin-form--wide" id="detailForm" enctype="multipart/form-data">
            <input type="hidden" name="story_id" value="<?= (int)($story['story_id'] ?? 0) ?>">

            <section class="story-cms-card">
                <div class="story-cms-card__header">
                    <h2 class="story-cms-card__title">Hero Section</h2>
                </div>
                <div class="story-cms-card__body">
                    <div class="story-cms-grid-2">

                        <div class="admin-field story-cms-col-full">
                            <label>Upload Hero Image</label>
                            <input
                                class="admin-input"
                                type="file"
                                name="hero_image_upload"
                                accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif"
                            >
                            <span class="story-cms-hint">JPG, PNG, WebP, or GIF — max 10 MB. Leave empty to use path below.</span>
                        </div>

                        <div class="admin-field story-cms-col-full">
                            <label>Hero Image <span class="story-cms-live-badge">live preview</span></label>
                            <div class="story-cms-preview" id="heroPreviewWrap">
                                <?php if (!empty($detailPage['hero_image'])): ?>
                                    <img id="heroPreviewImg" src="<?= h($detailPage['hero_image']) ?>" alt="Hero preview">
                                <?php else: ?>
                                    <div class="story-cms-preview-empty" id="heroPreviewEmpty">
                                        <span>🌅</span>
                                        No image yet — enter a path below
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input
                                class="admin-input js-preview"
                                data-target="heroPreviewImg"
                                data-empty="heroPreviewEmpty"
                                type="text"
                                name="hero_image"
                                value="<?= h($detailPage['hero_image'] ?? '') ?>"
                                placeholder="/images/Stories/details/your-hero.jpg"
                            >
                            <span class="story-cms-hint">Path relative to your public folder. Or upload file above.</span>
                        </div>

                        <div class="admin-field story-cms-col-full">
                            <label>Hero Heading</label>
                            <input
                                class="admin-input"
                                type="text"
                                name="hero_heading"
                                value="<?= h($detailPage['hero_heading'] ?? '') ?>"
                                placeholder="e.g. OMDENKEN – LIVE PODCAST SESSION"
                            >
                        </div>

                        <div class="admin-field story-cms-col-full">
                            <label>Hero Description</label>
                            <textarea
                                class="admin-input admin-textarea"
                                name="hero_description"
                                placeholder="Short intro shown on the hero..."
                            ><?= h($detailPage['hero_description'] ?? '') ?></textarea>
                        </div>

                    </div>
                </div>
            </section>

            <section class="story-cms-card">
                <div class="story-cms-card__header">
                    <h2 class="story-cms-card__title">Main Article</h2>
                </div>
                <div class="story-cms-card__body">
                    <div class="story-cms-grid-2">

                        <div class="admin-field story-cms-col-full">
                            <label>Article Title</label>
                            <input
                                class="admin-input"
                                type="text"
                                name="article_title"
                                value="<?= h($detailPage['article_title'] ?? '') ?>"
                                placeholder="e.g. Changing perspectives through real stories."
                            >
                        </div>

                        <div class="admin-field story-cms-col-full">
                            <label>Upload Article Image</label>
                            <input
                                class="admin-input"
                                type="file"
                                name="article_image_upload"
                                accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif"
                            >
                            <span class="story-cms-hint">JPG, PNG, WebP, or GIF — max 10 MB. Leave empty to use path below.</span>
                        </div>

                        <div class="admin-field story-cms-col-full">
                            <label>Article Image <span class="story-cms-live-badge">live preview</span></label>
                            <div class="story-cms-preview" id="articlePreviewWrap">
                                <?php if (!empty($detailPage['article_image'])): ?>
                                    <img id="articlePreviewImg" src="<?= h($detailPage['article_image']) ?>" alt="Article preview">
                                <?php else: ?>
                                    <div class="story-cms-preview-empty" id="articlePreviewEmpty">
                                        <span>📷</span>
                                        No image yet
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input
                                class="admin-input js-preview"
                                data-target="articlePreviewImg"
                                data-empty="articlePreviewEmpty"
                                type="text"
                                name="article_image"
                                value="<?= h($detailPage['article_image'] ?? '') ?>"
                                placeholder="/images/Stories/details/article.jpg"
                            >
                            <span class="story-cms-hint">Or upload file above.</span>
                        </div>

                        <div class="admin-field story-cms-col-full">
                            <label>Image Caption</label>
                            <input
                                class="admin-input"
                                type="text"
                                name="article_image_caption"
                                value="<?= h($detailPage['article_image_caption'] ?? '') ?>"
                                placeholder="Short caption under the image"
                            >
                        </div>

                        <div class="admin-field story-cms-col-full">
                            <label>Paragraph 1</label>
                            <textarea class="admin-input admin-textarea" name="article_paragraph_1" placeholder="First paragraph..."><?= h($detailPage['article_paragraph_1'] ?? '') ?></textarea>
                        </div>

                        <div class="admin-field story-cms-col-full">
                            <label>Paragraph 2</label>
                            <textarea class="admin-input admin-textarea" name="article_paragraph_2" placeholder="Second paragraph..."><?= h($detailPage['article_paragraph_2'] ?? '') ?></textarea>
                        </div>

                        <div class="admin-field story-cms-col-full">
                            <label>Paragraph 3</label>
                            <textarea class="admin-input admin-textarea" name="article_paragraph_3" placeholder="Third paragraph..."><?= h($detailPage['article_paragraph_3'] ?? '') ?></textarea>
                        </div>

                    </div>
                </div>
            </section>

            <section class="story-cms-card">
                <div class="story-cms-card__header">
                    <h2 class="story-cms-card__title">Highlights</h2>
                </div>
                <div class="story-cms-card__body">

                    <div class="story-cms-repeat-list" id="highlightsList">
                        <?php foreach ($highlights as $i => $item): ?>
                            <div class="story-cms-repeat-item repeat-item" data-type="highlight">
                                <div class="story-cms-repeat-head">
                                    <span class="story-cms-repeat-label">
                                        Highlight <span class="item-num"><?= $i + 1 ?></span>
                                    </span>
                                    <button type="button" class="admin-btn admin-btn-sm admin-btn-danger js-remove story-cms-remove">
                                        Remove
                                    </button>
                                </div>

                                <div class="story-cms-grid-2">
                                    <div class="admin-field">
                                        <label>Icon</label>
                                        <input
                                            class="admin-input"
                                            type="text"
                                            name="highlights[<?= $i ?>][icon]"
                                            value="<?= h($item['icon'] ?? '') ?>"
                                            placeholder="✓ or emoji"
                                        >
                                    </div>

                                    <div class="admin-field">
                                        <label>Title</label>
                                        <input
                                            class="admin-input"
                                            type="text"
                                            name="highlights[<?= $i ?>][title]"
                                            value="<?= h($item['title'] ?? '') ?>"
                                            placeholder="Highlight title"
                                        >
                                    </div>

                                    <div class="admin-field story-cms-col-full">
                                        <label>Description</label>
                                        <textarea
                                            class="admin-input admin-textarea"
                                            name="highlights[<?= $i ?>][description]"
                                            placeholder="Describe this highlight..."
                                        ><?= h($item['description'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="admin-btn admin-btn-secondary" id="addHighlight">
                        ＋ Add Highlight
                    </button>
                </div>
            </section>

            <section class="story-cms-card">
                <div class="story-cms-card__header">
                    <h2 class="story-cms-card__title">Gallery</h2>
                </div>
                <div class="story-cms-card__body">

                    <div class="story-cms-repeat-list" id="galleryList">
                        <?php foreach ($gallery as $i => $item): ?>
                            <div class="story-cms-repeat-item repeat-item" data-type="gallery">
                                <div class="story-cms-repeat-head">
                                    <span class="story-cms-repeat-label">
                                        Gallery Item <span class="item-num"><?= $i + 1 ?></span>
                                    </span>
                                    <button type="button" class="admin-btn admin-btn-sm admin-btn-danger js-remove story-cms-remove">
                                        Remove
                                    </button>
                                </div>

                                <div class="story-cms-grid-2">
                                    <div class="admin-field story-cms-col-full">
                                        <label>Image <span class="story-cms-live-badge">live preview</span></label>
                                        <div class="story-cms-preview gallery-preview-wrap">
                                            <?php if (!empty($item['image'])): ?>
                                                <img class="gallery-preview-img" src="<?= h($item['image']) ?>" alt="Gallery preview">
                                            <?php else: ?>
                                                <div class="story-cms-preview-empty gallery-preview-empty">
                                                    <span>🖼</span>
                                                    No image yet
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <input
                                            class="admin-input js-gallery-preview"
                                            type="text"
                                            name="gallery[<?= $i ?>][image]"
                                            value="<?= h($item['image'] ?? '') ?>"
                                            placeholder="/images/Stories/details/gallery-1.jpg"
                                        >
                                    </div>

                                    <div class="admin-field">
                                        <label>Heading</label>
                                        <input
                                            class="admin-input"
                                            type="text"
                                            name="gallery[<?= $i ?>][heading]"
                                            value="<?= h($item['heading'] ?? '') ?>"
                                            placeholder="Gallery item heading"
                                        >
                                    </div>

                                    <div class="admin-field">
                                        <label>Text</label>
                                        <textarea
                                            class="admin-input admin-textarea"
                                            name="gallery[<?= $i ?>][text]"
                                            placeholder="Caption or description..."
                                        ><?= h($item['text'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="admin-btn admin-btn-secondary" id="addGallery">
                        ＋ Add Gallery Item
                    </button>
                </div>
            </section>

            <div class="admin-form-actions">
                <a href="/cms/stories" class="admin-btn admin-btn-secondary">← Back</a>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <?= $isEdit ? '💾 Save Changes' : '✨ Create Detail Page' ?>
                </button>
            </div>
        </form>

    </div>
</main>

<script>
(function () {
    document.querySelectorAll('.js-preview').forEach(input => {
        input.addEventListener('input', () => {
            const val = input.value.trim();
            let imgEl = document.getElementById(input.dataset.target);
            const emptyEl = document.getElementById(input.dataset.empty);
            const wrap = input.closest('.admin-field')?.querySelector('.story-cms-preview');

            if (!wrap) return;

            if (val) {
                if (!imgEl) {
                    imgEl = document.createElement('img');
                    imgEl.id = input.dataset.target;
                    wrap.appendChild(imgEl);
                }
                imgEl.src = val;
                imgEl.style.display = 'block';
                if (emptyEl) emptyEl.style.display = 'none';
            } else {
                if (imgEl) imgEl.style.display = 'none';
                if (emptyEl) emptyEl.style.display = 'block';
            }
        });
    });

    function bindGalleryPreview(input) {
        input.addEventListener('input', () => {
            const val = input.value.trim();
            const wrap = input.closest('.repeat-item');
            if (!wrap) return;

            let img = wrap.querySelector('.gallery-preview-img');
            let empty = wrap.querySelector('.gallery-preview-empty');
            const previewWrap = wrap.querySelector('.gallery-preview-wrap');

            if (val) {
                if (!img) {
                    img = document.createElement('img');
                    img.className = 'gallery-preview-img';
                    previewWrap.appendChild(img);
                }
                img.src = val;
                img.style.display = 'block';
                if (empty) empty.style.display = 'none';
            } else {
                if (img) img.style.display = 'none';
                if (empty) empty.style.display = 'block';
            }
        });
    }

    document.querySelectorAll('.js-gallery-preview').forEach(bindGalleryPreview);

    function renumber(list) {
        list.querySelectorAll('.repeat-item').forEach((item, i) => {
            const num = item.querySelector('.item-num');
            if (num) num.textContent = i + 1;

            item.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
            });
        });
    }

    function bindRemove(btn) {
        btn.addEventListener('click', () => {
            const item = btn.closest('.repeat-item');
            const list = item.closest('.story-cms-repeat-list');

            if (list.querySelectorAll('.repeat-item').length <= 1) {
                alert('You need at least one item. Clear the fields instead of removing.');
                return;
            }

            item.remove();
            renumber(list);
        });
    }

    document.querySelectorAll('.js-remove').forEach(bindRemove);

    document.getElementById('addHighlight').addEventListener('click', () => {
        const list = document.getElementById('highlightsList');
        const count = list.querySelectorAll('.repeat-item').length;

        const html = `
            <div class="story-cms-repeat-item repeat-item" data-type="highlight">
                <div class="story-cms-repeat-head">
                    <span class="story-cms-repeat-label">
                        Highlight <span class="item-num">${count + 1}</span>
                    </span>
                    <button type="button" class="admin-btn admin-btn-sm admin-btn-danger js-remove story-cms-remove">
                        Remove
                    </button>
                </div>

                <div class="story-cms-grid-2">
                    <div class="admin-field">
                        <label>Icon</label>
                        <input class="admin-input" type="text" name="highlights[${count}][icon]" placeholder="✓ or emoji">
                    </div>
                    <div class="admin-field">
                        <label>Title</label>
                        <input class="admin-input" type="text" name="highlights[${count}][title]" placeholder="Highlight title">
                    </div>
                    <div class="admin-field story-cms-col-full">
                        <label>Description</label>
                        <textarea class="admin-input admin-textarea" name="highlights[${count}][description]" placeholder="Describe this highlight..."></textarea>
                    </div>
                </div>
            </div>`;

        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newItem = temp.firstElementChild;

        list.appendChild(newItem);
        bindRemove(newItem.querySelector('.js-remove'));
        newItem.querySelector('input').focus();
        renumber(list);
    });

    document.getElementById('addGallery').addEventListener('click', () => {
        const list = document.getElementById('galleryList');
        const count = list.querySelectorAll('.repeat-item').length;

        const html = `
            <div class="story-cms-repeat-item repeat-item" data-type="gallery">
                <div class="story-cms-repeat-head">
                    <span class="story-cms-repeat-label">
                        Gallery Item <span class="item-num">${count + 1}</span>
                    </span>
                    <button type="button" class="admin-btn admin-btn-sm admin-btn-danger js-remove story-cms-remove">
                        Remove
                    </button>
                </div>

                <div class="story-cms-grid-2">
                    <div class="admin-field story-cms-col-full">
                        <label>Image <span class="story-cms-live-badge">live preview</span></label>
                        <div class="story-cms-preview gallery-preview-wrap">
                            <div class="story-cms-preview-empty gallery-preview-empty">
                                <span>🖼</span>
                                No image yet
                            </div>
                        </div>
                        <input class="admin-input js-gallery-preview" type="text" name="gallery[${count}][image]" placeholder="/images/Stories/details/gallery.jpg">
                    </div>
                    <div class="admin-field">
                        <label>Heading</label>
                        <input class="admin-input" type="text" name="gallery[${count}][heading]" placeholder="Gallery item heading">
                    </div>
                    <div class="admin-field">
                        <label>Text</label>
                        <textarea class="admin-input admin-textarea" name="gallery[${count}][text]" placeholder="Caption or description..."></textarea>
                    </div>
                </div>
            </div>`;

        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newItem = temp.firstElementChild;

        list.appendChild(newItem);
        bindRemove(newItem.querySelector('.js-remove'));
        bindGalleryPreview(newItem.querySelector('.js-gallery-preview'));
        newItem.querySelector('input').focus();
        renumber(list);
    });
})();
</script>

</body>
</html>