<?php
if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

$isCreate = empty($story['story_id']);
$pageTitle = $isCreate ? 'Add New Story' : 'Edit Story';
$formAction = $isCreate ? '/cms/stories/store' : '/cms/stories/update';
$submitButton = $isCreate ? '✨ Create Story' : '💾 Save Story';
$csrfToken = $isCreate ? 'admin_stories_create' : 'admin_stories_edit';
$formNote = $isCreate ? 'All fields with * are required.' : 'All changes are saved immediately on submit.';

$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../../partials/head.php'; ?>
<body class="<?= h($bodyClass) ?>">

<?php require __DIR__ . '/../../partials/header.php'; ?>

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
                    <?php if ($isCreate): ?>
                        Create a new story card that will appear on the public site.
                    <?php else: ?>
                        Update the story card details shown on the public site.
                    <?php endif; ?>
                </p>
            </div>

            <?php if (!$isCreate && !empty($story['name'])): ?>
                <div class="story-cms-badge">📖 <?= h($story['name']) ?></div>
            <?php elseif ($isCreate): ?>
                <div class="story-cms-badge">✨ New Story</div>
            <?php endif; ?>
        </div>

        <form method="post" action="<?= h($formAction) ?>" class="admin-form admin-form--wide" enctype="multipart/form-data">
            <input type="hidden" name="story_id" value="<?= (int)($story['story_id'] ?? 0) ?>">
            <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">

            <section class="story-cms-card">
                <div class="story-cms-card__header">
                    <h2 class="story-cms-card__title">Basic Information</h2>
                </div>
                <div class="story-cms-card__body">

                    <div class="story-cms-grid-2">
                        <div class="admin-field">
                            <label for="name">Story Name <?php if ($isCreate): ?>*<?php endif; ?></label>
                            <input
                                class="admin-input <?= !empty($errors['name']) ? 'has-error' : '' ?>"
                                type="text"
                                id="name"
                                name="name"
                                value="<?= h($story['name'] ?? '') ?>"
                                placeholder="e.g. Winnie de Poeh"
                                <?php if ($isCreate): ?>required<?php endif; ?>
                            >
                            <?php if (!empty($errors['name'])): ?>
                                <span class="field-error"><?= h($errors['name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="admin-field">
                            <label for="slug">Slug <?php if ($isCreate): ?>*<?php endif; ?></label>
                            <input
                                class="admin-input <?= !empty($errors['slug']) ? 'has-error' : '' ?>"
                                type="text"
                                id="slug"
                                name="slug"
                                value="<?= h($story['slug'] ?? '') ?>"
                                placeholder="e.g. winnie-de-poeh"
                                <?php if ($isCreate): ?>required<?php endif; ?>
                            >
                            <span class="story-cms-hint">Used in the URL — lowercase, hyphens only.</span>
                            <?php if (!empty($errors['slug'])): ?>
                                <span class="field-error"><?= h($errors['slug']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="admin-field">
                        <label for="description">Description <?php if ($isCreate): ?>*<?php endif; ?></label>
                        <textarea
                            class="admin-input admin-textarea <?= !empty($errors['description']) ? 'has-error' : '' ?>"
                            id="description"
                            name="description"
                            placeholder="Short description shown on the story card..."
                            <?php if ($isCreate): ?>required<?php endif; ?>
                        ><?= h($story['description'] ?? '') ?></textarea>
                        <?php if (!empty($errors['description'])): ?>
                            <span class="field-error"><?= h($errors['description']) ?></span>
                        <?php endif; ?>
                    </div>

                </div>
            </section>

            <section class="story-cms-card">
                <div class="story-cms-card__header">
                    <h2 class="story-cms-card__title">Card Image <?php if ($isCreate): ?>*<?php endif; ?></h2>
                </div>
                <div class="story-cms-card__body">

                    <div class="admin-field">
                        <label for="image_upload">Upload Story Image</label>
                        <input
                            class="admin-input"
                            type="file"
                            id="image_upload"
                            name="image_upload"
                            accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif"
                        >
                        <span class="story-cms-hint">JPG, PNG, WebP, or GIF — max 10 MB. <?php if ($isCreate): ?>Either upload or enter path below.<?php else: ?>Leave empty to keep current image.<?php endif; ?></span>
                    </div>

                    <div class="admin-field">
                        <label for="image_path">Or Image Path <?php if ($isCreate): ?>*<?php endif; ?></label>
                        <input
                            class="admin-input <?= !empty($errors['image_path']) ? 'has-error' : '' ?>"
                            type="text"
                            id="image_path"
                            name="image_path"
                            value="<?= h($story['image_path'] ?? '') ?>"
                            placeholder="/images/Stories/cards/my-story.jpg"
                            oninput="previewImg(this.value)"
                            <?php if ($isCreate): ?>required<?php endif; ?>
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
                            <label for="story_type">Story Type <?php if ($isCreate): ?>*<?php endif; ?></label>
                            <select
                                class="admin-input <?= !empty($errors['story_type']) ? 'has-error' : '' ?>"
                                id="story_type"
                                name="story_type"
                                <?php if ($isCreate): ?>required<?php endif; ?>
                            >
                                <option value="">— Select Type —</option>
                                <option value="historical" <?= ($story['story_type'] ?? '') === 'historical' ? 'selected' : '' ?>>Historical</option>
                                <option value="fictional" <?= ($story['story_type'] ?? '') === 'fictional' ? 'selected' : '' ?>>Fictional</option>
                                <option value="cultural" <?= ($story['story_type'] ?? '') === 'cultural' ? 'selected' : '' ?>>Cultural</option>
                                <option value="interactive" <?= ($story['story_type'] ?? '') === 'interactive' ? 'selected' : '' ?>>Interactive</option>
                                <option value="podcast" <?= ($story['story_type'] ?? '') === 'podcast' ? 'selected' : '' ?>>Podcast</option>
                                <option value="story" <?= ($story['story_type'] ?? '') === 'story' ? 'selected' : '' ?>>Story</option>
                                <option value="kids" <?= ($story['story_type'] ?? '') === 'kids' ? 'selected' : '' ?>>Kids</option>
                            </select>
                            <?php if (!empty($errors['story_type'])): ?>
                                <span class="field-error"><?= h($errors['story_type']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="admin-field">
                            <label for="age">Age Group <?php if ($isCreate): ?>*<?php endif; ?></label>
                            <input
                                class="admin-input"
                                type="text"
                                id="age"
                                name="age"
                                value="<?= h($story['age'] ?? '') ?>"
                                placeholder="e.g. 4+, 16+, All ages"
                                <?php if ($isCreate): ?>required<?php endif; ?>
                            >
                            <?php if (!empty($errors['age'])): ?>
                                <span class="field-error"><?= h($errors['age']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="admin-field">
                            <label for="language">Language <?php if ($isCreate): ?>*<?php endif; ?></label>
                            <input
                                class="admin-input"
                                type="text"
                                id="language"
                                name="language"
                                value="<?= h($story['language'] ?? '') ?>"
                                placeholder="e.g. NL, ENG"
                                <?php if ($isCreate): ?>required<?php endif; ?>
                            >
                            <?php if (!empty($errors['language'])): ?>
                                <span class="field-error"><?= h($errors['language']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="admin-field">
                            <label for="template">Template</label>
                            <select
                                class="admin-input <?= !empty($errors['template']) ? 'has-error' : '' ?>"
                                id="template"
                                name="template"
                            >
                                <option value="generic" <?= ($story['template'] ?? 'generic') === 'generic' ? 'selected' : '' ?>>Generic</option>
                                <option value="omdenken" <?= ($story['template'] ?? '') === 'omdenken' ? 'selected' : '' ?>>Omdenken</option>
                                <option value="buurderij" <?= ($story['template'] ?? '') === 'buurderij' ? 'selected' : '' ?>>Buurderij</option>
                            </select>
                            <span class="story-cms-hint">Controls the detail page layout.</span>
                            <?php if (!empty($errors['template'])): ?>
                                <span class="field-error"><?= h($errors['template']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="admin-field" style="max-width:220px;">
                        <label for="audience">Audience</label>
                        <select
                            class="admin-input <?= !empty($errors['audience']) ? 'has-error' : '' ?>"
                            id="audience"
                            name="audience"
                        >
                            <option value="">— Optional —</option>
                            <option value="all-ages" <?= ($story['audience'] ?? '') === 'all-ages' ? 'selected' : '' ?>>All Ages</option>
                            <option value="kids" <?= ($story['audience'] ?? '') === 'kids' ? 'selected' : '' ?>>Kids</option>
                            <option value="teens" <?= ($story['audience'] ?? '') === 'teens' ? 'selected' : '' ?>>Teens</option>
                            <option value="adults" <?= ($story['audience'] ?? '') === 'adults' ? 'selected' : '' ?>>Adults</option>
                            <option value="families" <?= ($story['audience'] ?? '') === 'families' ? 'selected' : '' ?>>Families</option>
                        </select>
                        <?php if (!empty($errors['audience'])): ?>
                            <span class="field-error"><?= h($errors['audience']) ?></span>
                        <?php endif; ?>
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

                    <div class="story-cms-grid-3">
                        <div class="admin-field">
                            <label for="event_day">Story Day <?php if ($isCreate): ?>*<?php endif; ?></label>
                            <select
                                class="admin-input <?= !empty($errors['event_day']) ? 'has-error' : '' ?>"
                                id="event_day"
                                name="event_day"
                                <?php if ($isCreate): ?>required<?php endif; ?>
                            >
                                <option value="">— Select Day —</option>
                                <?php foreach (['thursday', 'friday', 'saturday', 'sunday'] as $day): ?>
                                    <option value="<?= h($day) ?>" <?= ($story['event_day'] ?? '') === $day ? 'selected' : '' ?>>
                                        <?= h(ucfirst($day)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($errors['event_day'])): ?>
                                <span class="field-error"><?= h($errors['event_day']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="admin-field">
                            <label for="start_time">Start Time <?php if ($isCreate): ?>*<?php endif; ?></label>
                            <input
                                class="admin-input <?= !empty($errors['start_time']) ? 'has-error' : '' ?>"
                                type="time"
                                id="start_time"
                                name="start_time"
                                value="<?= h($story['start_time'] ?? '') ?>"
                                <?php if ($isCreate): ?>required<?php endif; ?>
                            >
                            <?php if (!empty($errors['start_time'])): ?>
                                <span class="field-error"><?= h($errors['start_time']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="admin-field">
                            <label for="end_time">End Time <?php if ($isCreate): ?>*<?php endif; ?></label>
                            <input
                                class="admin-input <?= !empty($errors['end_time']) ? 'has-error' : '' ?>"
                                type="time"
                                id="end_time"
                                name="end_time"
                                value="<?= h($story['end_time'] ?? '') ?>"
                                <?php if ($isCreate): ?>required<?php endif; ?>
                            >
                            <?php if (!empty($errors['end_time'])): ?>
                                <span class="field-error"><?= h($errors['end_time']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </section>

            <div class="admin-form-actions">
                <span class="story-cms-form-note"><?= h($formNote) ?></span>
                <a href="/cms/stories" class="admin-btn admin-btn-secondary">Cancel</a>
                <button type="submit" class="admin-btn admin-btn-primary"><?= h($submitButton) ?></button>
            </div>
        </form>

    </div>
</main>

<script>
function previewImg(path) {
    var wrap = document.getElementById('imgPreview');
    var img  = document.getElementById('imgPreviewImg');

    if (path && path.trim()) {
        img.src = path.trim();
        wrap.style.display = 'flex';
        img.onerror = function () {
            wrap.style.display = 'none';
        };
        img.onload = function () {
            wrap.style.display = 'flex';
        };
    } else {
        wrap.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var pathInput = document.getElementById('image_path');
    if (pathInput && pathInput.value.trim()) {
        previewImg(pathInput.value.trim());
    }
});
</script>
<?php require __DIR__ . '/../../partials/footer.php'; ?>
</body>
</html>
