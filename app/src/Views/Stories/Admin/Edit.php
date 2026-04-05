<?php
if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Story — CMS</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:         #0f1117;
            --surface:    #181c27;
            --surface2:   #1f2436;
            --surface3:   #252a3a;
            --border:     rgba(255,255,255,0.07);
            --border2:    rgba(255,255,255,0.12);
            --accent:     #f59e0b;
            --accent-dim: rgba(245,158,11,0.12);
            --success:    #22c55e;
            --danger:     #ef4444;
            --danger-dim: rgba(239,68,68,0.12);
            --text:       #f1f5f9;
            --muted:      #64748b;
            --label:      #94a3b8;
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

        .topbar-logo  { font-size: 13px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--accent); }
        .topbar-sep   { color: var(--border); font-size: 20px; }
        .topbar-title { font-size: 13px; color: var(--muted); }

        .topbar-back {
            margin-left: auto;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            letter-spacing: .06em;
            text-transform: uppercase;
            transition: color .15s;
        }
        .topbar-back:hover { color: var(--text); }

        /* ── Page layout ── */
        .page {
            max-width: 860px;
            margin: 0 auto;
            padding: 40px 24px 80px;
        }

        /* ── Page header ── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .page-header-left h1 {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -.02em;
        }

        .page-header-left p {
            margin-top: 5px;
            font-size: 13px;
            color: var(--muted);
        }

        .story-name-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent-dim);
            border: 1px solid rgba(245,158,11,.25);
            color: var(--accent);
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .04em;
        }

        /* ── Card ── */
        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .form-card-header {
            background: var(--surface2);
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-card-header h2 {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--label);
        }

        .form-card-icon {
            font-size: 15px;
        }

        .form-card-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ── Grid layouts ── */
        .field-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .field-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
        }

        /* ── Field ── */
        .field { display: flex; flex-direction: column; gap: 6px; }

        .field label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--label);
        }

        .field input,
        .field textarea,
        .field select {
            width: 100%;
            background: var(--surface3);
            border: 1px solid var(--border2);
            border-radius: 8px;
            color: var(--text);
            font-size: 14px;
            font-family: inherit;
            padding: 10px 14px;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }

        .field input:focus,
        .field textarea:focus,
        .field select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(245,158,11,.12);
        }

        .field textarea {
            resize: vertical;
            min-height: 110px;
            line-height: 1.6;
        }

        .field input.has-error,
        .field textarea.has-error {
            border-color: var(--danger);
        }

        .field-error {
            font-size: 11px;
            color: var(--danger);
            font-weight: 600;
        }

        .field-hint {
            font-size: 11px;
            color: var(--muted);
            line-height: 1.5;
        }

        /* Image preview */
        .img-preview-wrap {
            margin-top: 8px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: var(--surface2);
            max-width: 200px;
            display: none;
        }

        .img-preview-wrap img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }

        /* ── Actions ── */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 20px 24px;
            background: var(--surface2);
            border-top: 1px solid var(--border);
        }

        .form-actions-left {
            font-size: 12px;
            color: var(--muted);
        }

        .form-actions-right { display: flex; gap: 10px; align-items: center; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .04em;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform .15s, opacity .15s;
            font-family: inherit;
        }
        .btn:hover { transform: translateY(-1px); opacity: .92; }

        .btn-save {
            background: var(--accent);
            color: #000;
        }

        .btn-cancel {
            background: rgba(255,255,255,0.06);
            color: var(--label);
            border: 1px solid var(--border2);
        }

        @media (max-width: 700px) {
            .field-grid-2,
            .field-grid-3 { grid-template-columns: 1fr; }
            .page { padding: 24px 16px 60px; }
        }
    </style>
</head>
<body>

<div class="topbar">
    <span class="topbar-logo">CMS</span>
    <span class="topbar-sep">/</span>
    <span class="topbar-title">Stories</span>
    <span class="topbar-sep">/</span>
    <span class="topbar-title">Edit Story</span>
    <a href="/cms/stories" class="topbar-back">← Back to stories</a>
</div>

<div class="page">

    <div class="page-header">
        <div class="page-header-left">
            <h1>Edit Story</h1>
            <p>Update the story card details shown on the public site.</p>
        </div>
        <?php if (!empty($story['name'])): ?>
            <div class="story-name-badge">
                📖 <?= h($story['name']) ?>
            </div>
        <?php endif; ?>
    </div>

    <form method="post" action="/cms/stories/update">
        <input type="hidden" name="story_id" value="<?= (int)($story['story_id'] ?? 0) ?>">

        <!-- ── Basic info ── -->
        <div class="form-card">
            <div class="form-card-header">
                <span class="form-card-icon">📝</span>
                <h2>Basic Information</h2>
            </div>
            <div class="form-card-body">

                <div class="field-grid-2">
                    <div class="field">
                        <label for="name">Story Name</label>
                        <input type="text" id="name" name="name"
                               value="<?= h($story['name'] ?? '') ?>"
                               class="<?= !empty($errors['name']) ? 'has-error' : '' ?>"
                               placeholder="e.g. Winnie de Poeh">
                        <?php if (!empty($errors['name'])): ?>
                            <span class="field-error"><?= h($errors['name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="slug">Slug</label>
                        <input type="text" id="slug" name="slug"
                               value="<?= h($story['slug'] ?? '') ?>"
                               class="<?= !empty($errors['slug']) ? 'has-error' : '' ?>"
                               placeholder="e.g. winnie-de-poeh">
                        <span class="field-hint">Used in the URL — lowercase, hyphens only.</span>
                        <?php if (!empty($errors['slug'])): ?>
                            <span class="field-error"><?= h($errors['slug']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"
                              class="<?= !empty($errors['description']) ? 'has-error' : '' ?>"
                              placeholder="Short description shown on the story card..."><?= h($story['description'] ?? '') ?></textarea>
                    <?php if (!empty($errors['description'])): ?>
                        <span class="field-error"><?= h($errors['description']) ?></span>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- ── Media ── -->
        <div class="form-card">
            <div class="form-card-header">
                <span class="form-card-icon">🖼️</span>
                <h2>Card Image</h2>
            </div>
            <div class="form-card-body">

                <div class="field">
                    <label for="image_path">Image Path</label>
                    <input type="text" id="image_path" name="image_path"
                           value="<?= h($story['image_path'] ?? '') ?>"
                           class="<?= !empty($errors['image_path']) ? 'has-error' : '' ?>"
                           placeholder="/images/Stories/cards/my-story.jpg"
                           oninput="previewImg(this.value)">
                    <span class="field-hint">Path relative to /public — e.g. /images/Stories/cards/story.jpg</span>
                    <?php if (!empty($errors['image_path'])): ?>
                        <span class="field-error"><?= h($errors['image_path']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="img-preview-wrap" id="imgPreview">
                    <img id="imgPreviewImg" src="" alt="Preview">
                </div>

            </div>
        </div>

        <!-- ── Event details ── -->
        <div class="form-card">
            <div class="form-card-header">
                <span class="form-card-icon">🎭</span>
                <h2>Event Details</h2>
            </div>
            <div class="form-card-body">

                <div class="field-grid-3">
                    <div class="field">
                        <label for="story_type">Story Type</label>
                        <input type="text" id="story_type" name="story_type"
                               value="<?= h($story['story_type'] ?? '') ?>"
                               placeholder="e.g. Podcast, Story, Kids">
                        <?php if (!empty($errors['story_type'])): ?>
                            <span class="field-error"><?= h($errors['story_type']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="age">Age Group</label>
                        <input type="text" id="age" name="age"
                               value="<?= h($story['age'] ?? '') ?>"
                               placeholder="e.g. 4+, 16+, All ages">
                        <?php if (!empty($errors['age'])): ?>
                            <span class="field-error"><?= h($errors['age']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="language">Language</label>
                        <input type="text" id="language" name="language"
                               value="<?= h($story['language'] ?? '') ?>"
                               placeholder="e.g. NL, ENG">
                        <?php if (!empty($errors['language'])): ?>
                            <span class="field-error"><?= h($errors['language']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="field" style="max-width:200px;">
                    <label for="event_id">Event ID</label>
                    <input type="number" id="event_id" name="event_id"
                           value="<?= (int)($story['event_id'] ?? 0) ?>"
                           placeholder="0">
                    <span class="field-hint">Links this story to a schedule event.</span>
                    <?php if (!empty($errors['event_id'])): ?>
                        <span class="field-error"><?= h($errors['event_id']) ?></span>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- ── Actions ── -->
        <div class="form-card">
            <div class="form-actions">
                <span class="form-actions-left">All changes are saved immediately on submit.</span>
                <div class="form-actions-right">
                    <a href="/cms/stories" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-save">💾 Save Story</button>
                </div>
            </div>
        </div>

    </form>

</div>

<script>
function previewImg(path) {
    const wrap = document.getElementById('imgPreview');
    const img  = document.getElementById('imgPreviewImg');
    if (path && path.trim()) {
        img.src = path.trim();
        wrap.style.display = 'block';
        img.onerror = () => { wrap.style.display = 'none'; };
        img.onload  = () => { wrap.style.display = 'block'; };
    } else {
        wrap.style.display = 'none';
    }
}

// Auto-preview on load if path already set
document.addEventListener('DOMContentLoaded', () => {
    const pathInput = document.getElementById('image_path');
    if (pathInput && pathInput.value.trim()) {
        previewImg(pathInput.value.trim());
    }
});
</script>

</body>
</html>