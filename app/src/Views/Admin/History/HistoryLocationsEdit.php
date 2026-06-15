<?php /** @var \App\ViewModels\AdminHistoryViewModel $viewModel */ ?>

<?php
$pageTitle = 'Edit Locations — Haarlem Festival';
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>"
      data-upload-csrf="<?= htmlspecialchars(App\Core\Csrf::token('cms_upload')) ?>">

<?php require __DIR__ . '/../../partials/header.php'; ?>

<main class="admin-cms-wrap">

    <h1>Edit Locations page (CMS)</h1>

    <?php if ($viewModel->success !== null): ?>
        <div class="admin-cms-msg-ok"><?= htmlspecialchars($viewModel->success) ?></div>
    <?php endif; ?>

    <?php if ($viewModel->error !== null): ?>
        <div class="admin-cms-msg-err"><?= htmlspecialchars($viewModel->error) ?></div>
    <?php endif; ?>

    <form method="post" action="/admin/cms/history-locations">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

        <?php foreach ($viewModel->formSections as $section): ?>
            <?php if (empty($section['fields'])): ?>
                <?php continue; ?>
            <?php endif; ?>

            <div class="admin-cms-section">
                <h2><?= htmlspecialchars($section['title']) ?></h2>

                <?php foreach ($section['fields'] as $field): ?>
                    <div class="admin-cms-field">
                        <label><?= htmlspecialchars($field['label']) ?></label>
                        <?php if ($field['type'] === 'text'): ?>
                            <input
                                type="text"
                                name="<?= htmlspecialchars($field['name']) ?>"
                                value="<?= htmlspecialchars($field['value']) ?>"
                            >

                        <?php elseif ($field['type'] === 'textarea'): ?>
                            <textarea
                                name="<?= htmlspecialchars($field['name']) ?>"
                            ><?= htmlspecialchars($field['value']) ?></textarea>

                        <?php elseif ($field['type'] === 'wysiwyg'): ?>
                            <textarea
                                name="<?= htmlspecialchars($field['name']) ?>"
                                class="cms-wysiwyg"
                            ><?= $field['value'] ?></textarea>

                        <?php elseif ($field['type'] === 'hidden'): ?>
                            <input
                                type="hidden"
                                name="<?= htmlspecialchars($field['name']) ?>"
                                value="<?= htmlspecialchars($field['value']) ?>"
                            >

                        <?php elseif ($field['type'] === 'image'): ?>
                            <input
                                type="hidden"
                                id="image-id-<?= $field['name'] ?>"
                                name="<?= htmlspecialchars($field['name']) ?>"
                                value="<?= htmlspecialchars($field['value']) ?>"
                            >

                            <button
                                type="button"
                                class="btn btn-outline history-media-btn"
                                data-target="image-id-<?= $field['name'] ?>"
                            >
                                Choose image
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script src="/js/admin-cms-editors.js?v=1"></script>
<?php require __DIR__ . '/../History/history_media_modal.php'; ?>
<script src="/js/admin-history-media.js?v=1"></script>

<?php require __DIR__ . '/../../partials/footer.php'; ?>
</body>
</html>