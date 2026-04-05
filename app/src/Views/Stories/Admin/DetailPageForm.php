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
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0f1117;
            --surface: #181c27;
            --surface2: #1f2436;
            --border: rgba(255,255,255,0.07);
            --accent: #f59e0b;
            --accent-dim: rgba(245,158,11,0.10);
            --danger: #ef4444;
            --danger-dim: rgba(239,68,68,0.10);
            --success: #22c55e;
            --text: #f1f5f9;
            --muted: #64748b;
            --input-bg: #1a1f2e;
            --radius: 14px;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── Topbar ── */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            height: 60px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-logo { font-size: 13px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--accent); }
        .topbar-sep  { color: var(--border); font-size: 20px; }
        .topbar-crumb { font-size: 13px; color: var(--muted); }
        .topbar-crumb a { color: var(--muted); text-decoration: none; }
        .topbar-crumb a:hover { color: var(--text); }

        /* ── Layout ── */
        .page {
            max-width: 980px;
            margin: 0 auto;
            padding: 40px 24px 100px;
        }

        /* ── Page header ── */
        .page-header { margin-bottom: 36px; }

        .page-header h1 {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -.02em;
        }

        .page-header h1 span { color: var(--accent); }

        .page-header p {
            margin-top: 8px;
            font-size: 14px;
            color: var(--muted);
        }

        /* ── Section card ── */
        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 28px;
            margin-bottom: 24px;
        }

        .section-card-title {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Grid ── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 20px; }
        .col-full { grid-column: 1 / -1; }

        @media (max-width: 700px) {
            .grid-2 { grid-template-columns: 1fr; }
            .col-full { grid-column: 1; }
        }

        /* ── Form fields ── */
        .field { display: flex; flex-direction: column; gap: 7px; }

        .field label {
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .field input,
        .field textarea {
            background: var(--input-bg);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 14px;
            color: var(--text);
            outline: none;
            font-family: inherit;
            transition: border-color .2s, box-shadow .2s;
            width: 100%;
        }

        .field input:focus,
        .field textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.12);
        }

        .field textarea { min-height: 110px; resize: vertical; }

        .field-hint {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
        }

        /* ── Image preview ── */
        .preview-wrap {
            border: 1.5px dashed rgba(255,255,255,0.1);
            border-radius: 12px;
            overflow: hidden;
            background: var(--surface2);
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        .preview-wrap img {
            width: 100%;
            max-height: 260px;
            object-fit: cover;
            display: block;
        }

        .preview-empty {
            text-align: center;
            color: var(--muted);
            font-size: 13px;
            padding: 20px;
        }

        .preview-empty span { display: block; font-size: 28px; margin-bottom: 6px; }

        /* ── Repeat items (highlights / gallery) ── */
        .repeat-list { display: flex; flex-direction: column; gap: 16px; }

        .repeat-item {
            background: var(--surface2);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius);
            padding: 20px;
            position: relative;
            animation: fadeIn .2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .repeat-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .repeat-item-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
        }

        .btn-remove {
            background: var(--danger-dim);
            color: var(--danger);
            border: 1px solid rgba(239,68,68,.25);
            border-radius: 7px;
            padding: 5px 11px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: transform .15s, opacity .15s;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-remove:hover { transform: translateY(-1px); opacity: .9; }

        /* ── Add row button ── */
        .btn-add-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 13px;
            border: 1.5px dashed rgba(245,158,11,0.35);
            border-radius: var(--radius);
            background: var(--accent-dim);
            color: var(--accent);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s, border-color .2s;
            margin-top: 4px;
        }

        .btn-add-row:hover {
            background: rgba(245,158,11,0.18);
            border-color: var(--accent);
        }

        /* ── Bottom actions ── */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 32px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 13px 24px;
            border-radius: 11px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: transform .15s, opacity .15s;
            letter-spacing: .02em;
        }

        .btn:hover { transform: translateY(-1px); opacity: .92; }

        .btn-secondary {
            background: var(--surface2);
            color: var(--muted);
            border: 1px solid var(--border);
        }

        .btn-primary {
            background: var(--accent);
            color: #000;
        }

        /* ── Preview live update indicator ── */
        .live-badge {
            font-size: 10px;
            font-weight: 700;
            background: rgba(34,197,94,.15);
            color: var(--success);
            border: 1px solid rgba(34,197,94,.25);
            border-radius: 999px;
            padding: 2px 8px;
            margin-left: 8px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="topbar">
    <span class="topbar-logo">CMS</span>
    <span class="topbar-sep">/</span>
    <span class="topbar-crumb">
        <a href="/cms/stories">Stories</a> / <?= h($pageTitle) ?>
    </span>
</div>

<div class="page">

    <div class="page-header">
        <h1><?= h($isEdit ? 'Editing' : 'Creating') ?> detail page for <span><?= h($storyName) ?></span></h1>
        <p>
            <?php if ($isEdit): ?>
                Changes update the live detail page immediately after saving.
            <?php else: ?>
                Fill in the sections below to create a custom detail page for this story.
            <?php endif; ?>
        </p>
    </div>

    <form method="post" action="/cms/stories/detail-page/save" id="detailForm">
        <input type="hidden" name="story_id" value="<?= (int)($story['story_id'] ?? 0) ?>">

        <!-- ── Hero Section ── -->
        <div class="section-card">
            <div class="section-card-title">🖼 Hero Section</div>

            <div class="grid-2">
                <div class="field col-full">
                    <label>Hero Image <span class="live-badge">live preview</span></label>
                    <div class="preview-wrap" id="heroPreviewWrap">
                        <?php if (!empty($detailPage['hero_image'])): ?>
                            <img id="heroPreviewImg" src="<?= h($detailPage['hero_image']) ?>" alt="Hero preview">
                        <?php else: ?>
                            <div class="preview-empty" id="heroPreviewEmpty">
                                <span>🌅</span>No image yet — enter a path below
                            </div>
                        <?php endif; ?>
                    </div>
                    <input class="js-preview" data-target="heroPreviewImg" data-empty="heroPreviewEmpty"
                           type="text" name="hero_image"
                           value="<?= h($detailPage['hero_image'] ?? '') ?>"
                           placeholder="/images/Stories/details/your-hero.jpg">
                    <div class="field-hint">Path relative to your public folder.</div>
                </div>

                <div class="field col-full">
                    <label>Hero Heading</label>
                    <input type="text" name="hero_heading"
                           value="<?= h($detailPage['hero_heading'] ?? '') ?>"
                           placeholder="e.g. OMDENKEN – LIVE PODCAST SESSION">
                </div>

                <div class="field col-full">
                    <label>Hero Description</label>
                    <textarea name="hero_description" placeholder="Short intro shown on the hero..."><?= h($detailPage['hero_description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- ── Article Section ── -->
        <div class="section-card">
            <div class="section-card-title">📝 Main Article</div>

            <div class="grid-2">
                <div class="field col-full">
                    <label>Article Title</label>
                    <input type="text" name="article_title"
                           value="<?= h($detailPage['article_title'] ?? '') ?>"
                           placeholder="e.g. Changing perspectives through real stories.">
                </div>

                <div class="field col-full">
                    <label>Article Image <span class="live-badge">live preview</span></label>
                    <div class="preview-wrap" id="articlePreviewWrap">
                        <?php if (!empty($detailPage['article_image'])): ?>
                            <img id="articlePreviewImg" src="<?= h($detailPage['article_image']) ?>" alt="Article preview">
                        <?php else: ?>
                            <div class="preview-empty" id="articlePreviewEmpty">
                                <span>📷</span>No image yet
                            </div>
                        <?php endif; ?>
                    </div>
                    <input class="js-preview" data-target="articlePreviewImg" data-empty="articlePreviewEmpty"
                           type="text" name="article_image"
                           value="<?= h($detailPage['article_image'] ?? '') ?>"
                           placeholder="/images/Stories/details/article.jpg">
                </div>

                <div class="field col-full">
                    <label>Image Caption</label>
                    <input type="text" name="article_image_caption"
                           value="<?= h($detailPage['article_image_caption'] ?? '') ?>"
                           placeholder="Short caption under the image">
                </div>

                <div class="field col-full">
                    <label>Paragraph 1</label>
                    <textarea name="article_paragraph_1" placeholder="First paragraph..."><?= h($detailPage['article_paragraph_1'] ?? '') ?></textarea>
                </div>

                <div class="field col-full">
                    <label>Paragraph 2</label>
                    <textarea name="article_paragraph_2" placeholder="Second paragraph..."><?= h($detailPage['article_paragraph_2'] ?? '') ?></textarea>
                </div>

                <div class="field col-full">
                    <label>Paragraph 3</label>
                    <textarea name="article_paragraph_3" placeholder="Third paragraph..."><?= h($detailPage['article_paragraph_3'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- ── Highlights ── -->
        <div class="section-card">
            <div class="section-card-title">✨ Highlights</div>

            <div class="repeat-list" id="highlightsList">
                <?php foreach ($highlights as $i => $item): ?>
                    <div class="repeat-item" data-type="highlight">
                        <div class="repeat-item-header">
                            <span class="repeat-item-label">Highlight <span class="item-num"><?= $i + 1 ?></span></span>
                            <button type="button" class="btn-remove js-remove">✕ Remove</button>
                        </div>
                        <div class="grid-2">
                            <div class="field">
                                <label>Icon</label>
                                <input type="text" name="highlights[<?= $i ?>][icon]"
                                       value="<?= h($item['icon'] ?? '') ?>"
                                       placeholder="✓ or emoji">
                            </div>
                            <div class="field">
                                <label>Title</label>
                                <input type="text" name="highlights[<?= $i ?>][title]"
                                       value="<?= h($item['title'] ?? '') ?>"
                                       placeholder="Highlight title">
                            </div>
                            <div class="field col-full">
                                <label>Description</label>
                                <textarea name="highlights[<?= $i ?>][description]"
                                          placeholder="Describe this highlight..."><?= h($item['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="btn-add-row" id="addHighlight">
                ＋ Add Highlight
            </button>
        </div>

        <!-- ── Gallery ── -->
        <div class="section-card">
            <div class="section-card-title">🖼 Gallery</div>

            <div class="repeat-list" id="galleryList">
                <?php foreach ($gallery as $i => $item): ?>
                    <div class="repeat-item" data-type="gallery">
                        <div class="repeat-item-header">
                            <span class="repeat-item-label">Gallery Item <span class="item-num"><?= $i + 1 ?></span></span>
                            <button type="button" class="btn-remove js-remove">✕ Remove</button>
                        </div>
                        <div class="grid-2">
                            <div class="field col-full">
                                <label>Image <span class="live-badge">live preview</span></label>
                                <div class="preview-wrap gallery-preview-wrap">
                                    <?php if (!empty($item['image'])): ?>
                                        <img class="gallery-preview-img" src="<?= h($item['image']) ?>" alt="Gallery preview">
                                    <?php else: ?>
                                        <div class="preview-empty gallery-preview-empty">
                                            <span>🖼</span>No image yet
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="text" name="gallery[<?= $i ?>][image]"
                                       class="js-gallery-preview"
                                       value="<?= h($item['image'] ?? '') ?>"
                                       placeholder="/images/Stories/details/gallery-1.jpg">
                            </div>
                            <div class="field">
                                <label>Heading</label>
                                <input type="text" name="gallery[<?= $i ?>][heading]"
                                       value="<?= h($item['heading'] ?? '') ?>"
                                       placeholder="Gallery item heading">
                            </div>
                            <div class="field">
                                <label>Text</label>
                                <textarea name="gallery[<?= $i ?>][text]"
                                          placeholder="Caption or description..."><?= h($item['text'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="btn-add-row" id="addGallery">
                ＋ Add Gallery Item
            </button>
        </div>

        <!-- ── Actions ── -->
        <div class="form-actions">
            <a href="/cms/stories" class="btn btn-secondary">← Back</a>
            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? '💾 Save Changes' : '✨ Create Detail Page' ?>
            </button>
        </div>

    </form>
</div>

<script>
(function () {

    // ─── Live image preview ───────────────────────────────────────────────
    document.querySelectorAll('.js-preview').forEach(input => {
        input.addEventListener('input', () => {
            const val   = input.value.trim();
            const imgEl = document.getElementById(input.dataset.target);
            const emptyEl = document.getElementById(input.dataset.empty);

            if (!imgEl) return;

            if (val) {
                imgEl.src = val;
                imgEl.style.display = 'block';
                if (emptyEl) emptyEl.style.display = 'none';
            } else {
                imgEl.style.display = 'none';
                if (emptyEl) emptyEl.style.display = 'block';
            }
        });
    });

    // ─── Gallery inline preview ───────────────────────────────────────────
    function bindGalleryPreview(input) {
        input.addEventListener('input', () => {
            const val  = input.value.trim();
            const wrap = input.closest('.repeat-item');
            if (!wrap) return;
            let img   = wrap.querySelector('.gallery-preview-img');
            let empty = wrap.querySelector('.gallery-preview-empty');

            if (val) {
                if (!img) {
                    img = document.createElement('img');
                    img.className = 'gallery-preview-img';
                    img.style.width = '100%';
                    img.style.maxHeight = '260px';
                    img.style.objectFit = 'cover';
                    wrap.querySelector('.gallery-preview-wrap').appendChild(img);
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

    // ─── Renumber items after add/remove ─────────────────────────────────
    function renumber(list, prefix) {
        list.querySelectorAll('.repeat-item').forEach((item, i) => {
            item.querySelector('.item-num').textContent = i + 1;
            item.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
            });
        });
    }

    // ─── Remove button ────────────────────────────────────────────────────
    function bindRemove(btn) {
        btn.addEventListener('click', () => {
            const item = btn.closest('.repeat-item');
            const list = item.closest('.repeat-list');
            // Keep at least 1 row
            if (list.querySelectorAll('.repeat-item').length <= 1) {
                alert('You need at least one item. Clear the fields instead of removing.');
                return;
            }
            item.remove();
            renumber(list);
        });
    }

    document.querySelectorAll('.js-remove').forEach(bindRemove);

    // ─── Add Highlight ────────────────────────────────────────────────────
    document.getElementById('addHighlight').addEventListener('click', () => {
        const list  = document.getElementById('highlightsList');
        const count = list.querySelectorAll('.repeat-item').length;

        const html = `
            <div class="repeat-item" data-type="highlight">
                <div class="repeat-item-header">
                    <span class="repeat-item-label">Highlight <span class="item-num">${count + 1}</span></span>
                    <button type="button" class="btn-remove js-remove">✕ Remove</button>
                </div>
                <div class="grid-2">
                    <div class="field">
                        <label>Icon</label>
                        <input type="text" name="highlights[${count}][icon]" placeholder="✓ or emoji">
                    </div>
                    <div class="field">
                        <label>Title</label>
                        <input type="text" name="highlights[${count}][title]" placeholder="Highlight title">
                    </div>
                    <div class="field col-full">
                        <label>Description</label>
                        <textarea name="highlights[${count}][description]" placeholder="Describe this highlight..."></textarea>
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

    // ─── Add Gallery Item ─────────────────────────────────────────────────
    document.getElementById('addGallery').addEventListener('click', () => {
        const list  = document.getElementById('galleryList');
        const count = list.querySelectorAll('.repeat-item').length;

        const html = `
            <div class="repeat-item" data-type="gallery">
                <div class="repeat-item-header">
                    <span class="repeat-item-label">Gallery Item <span class="item-num">${count + 1}</span></span>
                    <button type="button" class="btn-remove js-remove">✕ Remove</button>
                </div>
                <div class="grid-2">
                    <div class="field col-full">
                        <label>Image <span class="live-badge">live preview</span></label>
                        <div class="preview-wrap gallery-preview-wrap">
                            <div class="preview-empty gallery-preview-empty">
                                <span>🖼</span>No image yet
                            </div>
                        </div>
                        <input type="text" name="gallery[${count}][image]"
                               class="js-gallery-preview"
                               placeholder="/images/Stories/details/gallery.jpg">
                    </div>
                    <div class="field">
                        <label>Heading</label>
                        <input type="text" name="gallery[${count}][heading]" placeholder="Gallery item heading">
                    </div>
                    <div class="field">
                        <label>Text</label>
                        <textarea name="gallery[${count}][text]" placeholder="Caption or description..."></textarea>
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