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
            <span>Edit Story</span>
        </nav>

        <div class="story-cms-header">
            <div>
                <h1 class="admin-title">Edit Story</h1>
                <p class="admin-lead">Update the story card details shown on the public site.</p>
            </div>

            <?php if (!empty($story['name'])): ?>
                <div class="story-cms-badge">📖 <?= h($story['name']) ?></div>
            <?php endif; ?>
        </div>

        <form method="post" action="/cms/stories/update" class="admin-form admin-form--wide">
            <input type="hidden" name="story_id" value="<?= (int)($story['story_id'] ?? 0) ?>">

            <section class="story-cms-card">
                <div class="story-cms-card__header">
                    <h2 class="story-cms-card__title">Basic Information</h2>
                </div>
                <div class="story-cms-card__body">

                    <div class="story-cms-grid-2">
                        <div class="admin-field">
                            <label for="name">Story Name</label>
                            <input
                                class="admin-input <?= !empty($errors['name']) ? 'has-error' : '' ?>"
                                type="text"
                                id="name"
                                name="name"
                                value="<?= h($story['name'] ?? '') ?>"
                                placeholder="e.g. Winnie de Poeh"
                            >
                            <?php if (!empty($errors['name'])): ?>
                                <span class="field-error"><?= h($errors['name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="admin-field">
                            <label for="slug">Slug</label>
                            <input
                                class="admin-input <?= !empty($errors['slug']) ? 'has-error' : '' ?>"
                                type="text"
                                id="slug"
                                name="slug"
                                value="<?= h($story['slug'] ?? '') ?>"
                                placeholder="e.g. winnie-de-poeh"
                            >
                            <span class="story-cms-hint">Used in the URL — lowercase, hyphens only.</span>
                            <?php if (!empty($errors['slug'])): ?>
                                <span class="field-error"><?= h($errors['slug']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="admin-field">
                        <label for="description">Description</label>
                        <textarea
                            class="admin-input admin-textarea <?= !empty($errors['description']) ? 'has-error' : '' ?>"
                            id="description"
                            name="description"
                            placeholder="Short description shown on the story card..."
                        ><?= h($story['description'] ?? '') ?></textarea>
                        <?php if (!empty($errors['description'])): ?>
                            <span class="field-error"><?= h($errors['description']) ?></span>
                        <?php endif; ?>
                    </div>

                </div>
            </section>

            <section class="story-cms-card">
                <div class="story-cms-card__header">
                    <h2 class="story-cms-card__title">Card Image</h2>
                </div>
                <div class="story-cms-card__body">

                    <div class="admin-field">
                        <label for="image_path">Image Path</label>
                        <input
                            class="admin-input <?= !empty($errors['image_path']) ? 'has-error' : '' ?>"
                            type="text"
                            id="image_path"
                            name="image_path"
                            value="<?= h($story['image_path'] ?? '') ?>"
                            placeholder="/images/Stories/cards/my-story.jpg"
                            oninput="previewImg(this.value)"
                        >
                        <span class="story-cms-hint">Path relative to /public — for example: /images/Stories/cards/story.jpg</span>
                        <?php if (!empty($errors['image_path'])): ?>
                            <span class="field-error"><?= h($errors['image_path']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="story-cms-preview story-cms-preview--small" id="imgPreview" style="display:none;">
                        <img id="imgPreviewImg" src="" alt="Preview">
                    </div>

                </div>
            </section>

            <section class="story-cms-card">
                <div class="story-cms-card__header">
                    <h2 class="story-cms-card__title">Event Details</h2>
                </div>
                <div class="story-cms-card__body">

                    <div class="story-cms-grid-3">
                        <div class="admin-field">
                            <label for="story_type">Story Type</label>
                            <input
                                class="admin-input"
                                type="text"
                                id="story_type"
                                name="story_type"
                                value="<?= h($story['story_type'] ?? '') ?>"
                                placeholder="e.g. Podcast, Story, Kids"
                            >
                            <?php if (!empty($errors['story_type'])): ?>
                                <span class="field-error"><?= h($errors['story_type']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="admin-field">
                            <label for="age">Age Group</label>
                            <input
                                class="admin-input"
                                type="text"
                                id="age"
                                name="age"
                                value="<?= h($story['age'] ?? '') ?>"
                                placeholder="e.g. 4+, 16+, All ages"
                            >
                            <?php if (!empty($errors['age'])): ?>
                                <span class="field-error"><?= h($errors['age']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="admin-field">
                            <label for="language">Language</label>
                            <input
                                class="admin-input"
                                type="text"
                                id="language"
                                name="language"
                                value="<?= h($story['language'] ?? '') ?>"
                                placeholder="e.g. NL, ENG"
                            >
                            <?php if (!empty($errors['language'])): ?>
                                <span class="field-error"><?= h($errors['language']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="admin-field" style="max-width:220px;">
                        <label for="event_id">Event ID</label>
                        <input
                            class="admin-input"
                            type="number"
                            id="event_id"
                            name="event_id"
                            value="<?= (int)($story['event_id'] ?? 0) ?>"
                            placeholder="0"
                        >
                        <span class="story-cms-hint">Links this story to a schedule event.</span>
                        <?php if (!empty($errors['event_id'])): ?>
                            <span class="field-error"><?= h($errors['event_id']) ?></span>
                        <?php endif; ?>
                    </div>

                </div>
            </section>

            <div class="admin-form-actions">
                <span class="story-cms-form-note">All changes are saved immediately on submit.</span>
                <a href="/cms/stories" class="admin-btn admin-btn-secondary">Cancel</a>
                <button type="submit" class="admin-btn admin-btn-primary">💾 Save Story</button>
            </div>
        </form>

    </div>
</main>

<script>
function previewImg(path) {
    const wrap = document.getElementById('imgPreview');
    const img  = document.getElementById('imgPreviewImg');

    if (path && path.trim()) {
        img.src = path.trim();
        wrap.style.display = 'flex';
        img.onerror = () => { wrap.style.display = 'none'; };
        img.onload  = () => { wrap.style.display = 'flex'; };
    } else {
        wrap.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const pathInput = document.getElementById('image_path');
    if (pathInput && pathInput.value.trim()) {
        previewImg(pathInput.value.trim());
    }
});
</script>

</body>
</html>