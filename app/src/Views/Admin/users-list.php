<?php
/** @var \App\ViewModels\AdminUserViewModel $viewModel */

$success = \App\Core\Session::getFlash('admin_success');
$error   = \App\Core\Session::getFlash('admin_error');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users — Admin — <?= htmlspecialchars($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/admin.css?v=<?= htmlspecialchars($app['css_version'] ?? '1') ?>">
</head>
<body class="admin-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container">
        <nav class="admin-breadcrumb" aria-label="Breadcrumb">
            <a href="/"><?= htmlspecialchars($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Users</span>
        </nav>

        <section class="admin-page-header">
            <div>
                <h1 class="admin-title">Users</h1>
                <p class="admin-subtitle">Manage user accounts, roles and access.</p>
            </div>
        </section>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="admin-panel">
            <div class="admin-panel-top">
                <form method="GET" action="/admin/users" class="admin-search-form">
                    <div class="admin-search-group">
                        <input type="text" name="search"
                               value="<?= htmlspecialchars($viewModel->search) ?>"
                               placeholder="Search by name or email"
                               class="admin-input">
                    </div>
                    <div class="admin-search-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">Search</button>
                        <?php if ($viewModel->search !== ''): ?>
                            <a href="/admin/users" class="admin-btn admin-btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <?php if (!empty($viewModel->users)): ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>
                                <a href="<?= htmlspecialchars($viewModel->sortLink('first_name')) ?>" class="admin-sort-link">
                                    Name<?= htmlspecialchars($viewModel->sortArrow('first_name')) ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?= htmlspecialchars($viewModel->sortLink('email')) ?>" class="admin-sort-link">
                                    Email<?= htmlspecialchars($viewModel->sortArrow('email')) ?>
                                </a>
                            </th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>
                                <a href="<?= htmlspecialchars($viewModel->sortLink('created_at')) ?>" class="admin-sort-link">
                                    Registered<?= htmlspecialchars($viewModel->sortArrow('created_at')) ?>
                                </a>
                            </th>
                            <th class="admin-table-actions-head">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($viewModel->users as $uRow): ?>
                            <?php
                            $fullName  = trim(($uRow['first_name'] ?? '') . ' ' . ($uRow['last_name'] ?? ''));
                            $createdAt = !empty($uRow['created_at'])
                                ? date('d M Y', strtotime((string) $uRow['created_at']))
                                : '—';
                            ?>
                            <tr>
                                <td>
                                        <span class="admin-user-name">
                                            <?= htmlspecialchars($fullName !== '' ? $fullName : 'Unnamed user') ?>
                                        </span>
                                </td>
                                <td class="admin-user-email"><?= htmlspecialchars($uRow['email'] ?? '') ?></td>
                                <td>
                                    <span class="admin-role-pill"><?= htmlspecialchars($uRow['role_name'] ?? '—') ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($uRow['is_active'])): ?>
                                        <span class="admin-badge admin-badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="admin-badge admin-badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="admin-date-cell"><?= htmlspecialchars($createdAt) ?></td>
                                <td class="admin-table-actions">
                                    <a href="/admin/users/edit?id=<?= (int) ($uRow['user_id'] ?? 0) ?>"
                                       class="admin-btn admin-btn-small admin-btn-secondary">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="admin-empty-state">
                    <h2 class="admin-empty-title">No users found</h2>
                    <p class="admin-empty-text">Try changing the search query or clear the current filter.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>