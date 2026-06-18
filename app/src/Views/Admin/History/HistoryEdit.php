<?php /** @var \App\ViewModels\AdminHistoryViewModel $viewModel */ ?>

<?php
$pageTitle = 'Edit History — Haarlem Festival';
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

    <h1>Edit History page (CMS)</h1>

    <?php if ($viewModel->success !== null): ?>
        <div class="admin-cms-msg-ok"><?= htmlspecialchars($viewModel->success) ?></div>
    <?php endif; ?>

    <?php if ($viewModel->error !== null): ?>
        <div class="admin-cms-msg-err"><?= htmlspecialchars($viewModel->error) ?></div>
    <?php endif; ?>

    <form method="post" action="/admin/cms/history">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

        <?php foreach ($viewModel->formSections as $sectionIndex => $section): ?>
            <?php if (empty($section['fields'])): ?>
                <?php continue; ?>
            <?php endif; ?>

            <div class="admin-cms-section">
                <h2><?= htmlspecialchars($section['title']) ?></h2>

                <?php foreach ($section['fields'] as $fieldIndex => $field): ?>
                    <?php
                    $fieldName = (string)($field['name'] ?? '');
                    $fieldType = (string)($field['type'] ?? '');
                    $fieldLabel = (string)($field['label'] ?? '');
                    $fieldValue = (string)($field['value'] ?? '');
                    $fieldId = 'field-' . $sectionIndex . '-' . $fieldIndex;
                    $imageTargetId = 'image-id-' . $sectionIndex . '-' . $fieldIndex;
                    ?>

                    <div class="admin-cms-field">
                        <?php if ($fieldType !== 'hidden'): ?>
                            <label for="<?= htmlspecialchars($fieldId) ?>">
                                <?= htmlspecialchars($fieldLabel) ?>
                            </label>
                        <?php endif; ?>

                        <?php if ($fieldType === 'text'): ?>
                            <input
                                type="text"
                                id="<?= htmlspecialchars($fieldId) ?>"
                                name="<?= htmlspecialchars($fieldName) ?>"
                                value="<?= htmlspecialchars($fieldValue) ?>"
                            >

                        <?php elseif ($fieldType === 'textarea'): ?>
                            <textarea
                                id="<?= htmlspecialchars($fieldId) ?>"
                                name="<?= htmlspecialchars($fieldName) ?>"
                            ><?= htmlspecialchars($fieldValue) ?></textarea>

                        <?php elseif ($fieldType === 'wysiwyg'): ?>
                            <textarea
                                id="<?= htmlspecialchars($fieldId) ?>"
                                name="<?= htmlspecialchars($fieldName) ?>"
                                class="cms-wysiwyg"
                            ><?= $fieldValue ?></textarea>

                        <?php elseif ($fieldType === 'hidden'): ?>
                            <input
                                type="hidden"
                                id="<?= htmlspecialchars($fieldId) ?>"
                                name="<?= htmlspecialchars($fieldName) ?>"
                                value="<?= htmlspecialchars($fieldValue) ?>"
                            >

                        <?php elseif ($fieldType === 'image'): ?>
                            <input
                                type="hidden"
                                id="<?= htmlspecialchars($imageTargetId) ?>"
                                name="<?= htmlspecialchars($fieldName) ?>"
                                value="<?= htmlspecialchars($fieldValue) ?>"
                            >

                            <button
                                type="button"
                                class="btn btn-outline history-media-btn"
                                data-target="<?= htmlspecialchars($imageTargetId) ?>"
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