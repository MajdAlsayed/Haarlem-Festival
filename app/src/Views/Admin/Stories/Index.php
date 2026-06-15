<?php
if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

$pageTitle = 'Story Management — CMS';
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../../partials/head.php'; ?>
<body class="<?= h($bodyClass) ?>">

<?php require __DIR__ . '/../../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--wide">

        <nav class="admin-breadcrumb" aria-label="Breadcrumb">
            <a href="/admin">CMS</a>
            <span class="admin-breadcrumb-sep">/</span>
            <span>Stories</span>
        </nav>

        <div class="story-cms-header">
            <div>
                <h1 class="admin-title">Story Management</h1>
                <p class="admin-lead">Edit story cards and manage detail pages for all stories.</p>
                <div class="story-cms-count-pill">
                    <span>📚</span>
                    <span><?= count($stories) ?> stories</span>
                </div>
            </div>
        </div>

        <div class="story-cms-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:72px;">Image</th>
                            <th>Story</th>
                            <th class="col-type">Type</th>
                            <th class="col-age">Age</th>
                            <th style="width:130px;">Detail Page</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stories)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="story-cms-empty">
                                        <div class="story-cms-empty-icon">📭</div>
                                        <h3>No stories found</h3>
                                        <p>Run your StoriesSeeder to populate the database.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($stories as $story): ?>
                                <?php $detailExists = !empty($story['has_detail_page']); ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($story['image_path'])): ?>
                                            <img
                                                class="story-cms-thumb"
                                                src="<?= h($story['image_path']) ?>"
                                                alt="<?= h($story['name'] ?? '') ?>"
                                            >
                                        <?php else: ?>
                                            <div class="story-cms-thumb-placeholder">📖</div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="story-cms-name"><?= h($story['name'] ?? '—') ?></div>
                                        <div class="story-cms-slug">/<?= h($story['slug'] ?? '') ?></div>
                                    </td>

                                    <td class="col-type">
                                        <?php if (!empty($story['story_type'])): ?>
                                            <span class="story-cms-badge-soft story-cms-badge-type">
                                                <?= h($story['story_type']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="admin-muted">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="col-age">
                                        <?php if (!empty($story['age'])): ?>
                                            <span class="story-cms-badge-soft story-cms-badge-age">
                                                <?= h($story['age']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="admin-muted">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($detailExists): ?>
                                            <span class="story-cms-badge-soft story-cms-badge-ok">✓ Done</span>
                                        <?php else: ?>
                                            <span class="story-cms-badge-soft story-cms-badge-muted">— None</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="story-cms-actions">
                                            <a
                                                class="admin-btn admin-btn-sm admin-btn-secondary"
                                                href="/cms/stories/edit?id=<?= (int)$story['story_id'] ?>"
                                            >
                                                ✏️ Edit story
                                            </a>

                                            <?php if ($detailExists): ?>
                                                <a
                                                    class="admin-btn admin-btn-sm admin-btn-primary"
                                                    href="/cms/stories/detail-page?slug=<?= h($story['slug'] ?? '') ?>"
                                                >
                                                    📄 Edit detail
                                                </a>
                                            <?php else: ?>
                                                <a
                                                    class="admin-btn admin-btn-sm admin-btn-primary"
                                                    href="/cms/stories/detail-page?slug=<?= h($story['slug'] ?? '') ?>"
                                                >
                                                    ➕ Add detail
                                                </a>
                                            <?php endif; ?>

                                            <a
                                                class="admin-btn admin-btn-sm admin-btn-danger"
                                                href="/cms/stories/delete?id=<?= (int)$story['story_id'] ?>"
                                                onclick="return confirm('Delete “<?= h(addslashes($story['name'] ?? 'this story')) ?>”? This cannot be undone.')"
                                            >
                                                🗑 Remove
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>
<?php require __DIR__ . '/../../partials/footer.php'; ?>
</body>
</html>