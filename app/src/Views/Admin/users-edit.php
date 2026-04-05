<?php
/** @var \App\Models\User $user */
/** @var array $roles */
/** @var string $csrf */

$error   = \App\Core\Session::getFlash('admin_error');
$success = \App\Core\Session::getFlash('admin_success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User — Admin — <?= htmlspecialchars($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <a href="/admin/users">Users</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Edit User</span>
        </nav>

        <section class="admin-page-header">
            <div>
                <h1 class="admin-title">Edit User</h1>
                <p class="admin-subtitle">Update account details, access level and status.</p>
            </div>
        </section>

        <?php if ($error): ?>
            <div class="admin-alert admin-alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <section class="admin-panel admin-form-panel">
            <form method="POST" action="/admin/users/update" class="admin-form">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="user_id" value="<?= (int)$user->id ?>">

                <div class="admin-form-grid">
                    <div class="admin-field">
                        <label for="first_name" class="admin-label">First name</label>
                        <input
                                id="first_name"
                                type="text"
                                name="first_name"
                                class="admin-input"
                                value="<?= htmlspecialchars($user->firstName) ?>"
                                required
                        >
                    </div>

                    <div class="admin-field">
                        <label for="last_name" class="admin-label">Last name</label>
                        <input
                                id="last_name"
                                type="text"
                                name="last_name"
                                class="admin-input"
                                value="<?= htmlspecialchars($user->lastName) ?>"
                        >
                    </div>
                </div>

                <div class="admin-field">
                    <label for="email" class="admin-label">Email</label>
                    <input
                            id="email"
                            type="email"
                            name="email"
                            class="admin-input"
                            value="<?= htmlspecialchars($user->email) ?>"
                            required
                    >
                </div>

                <div class="admin-form-grid admin-form-grid--secondary">
                    <div class="admin-field">
                        <label for="role_id" class="admin-label">Role</label>
                        <select id="role_id" name="role_id" class="admin-input admin-select">
                            <?php foreach ($roles as $role): ?>
                                <option
                                        value="<?= (int)$role['role_id'] ?>"
                                    <?= $user->roleId === (int)$role['role_id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admin-field admin-field-checkbox-wrap">
                        <label class="admin-checkbox">
                            <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                <?= $user->isActive ? 'checked' : '' ?>
                            >
                            <span>Active user</span>
                        </label>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Save changes</button>
                    <a href="/admin/users" class="admin-btn admin-btn-secondary">Cancel</a>
                </div>
            </form>
        </section>

        <section class="admin-panel admin-danger-panel">
            <div class="admin-danger-panel__content">
                <div>
                    <h2 class="admin-section-title">Danger zone</h2>
                    <p class="admin-section-text">
                        Deleting a user is permanent and should only be done when absolutely necessary.
                    </p>
                </div>

                <form
                        method="POST"
                        action="/admin/users/delete"
                        class="admin-delete-form"
                        onsubmit="return confirm('Delete this user?')"
                >
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="user_id" value="<?= (int)$user->id ?>">
                    <button type="submit" class="admin-btn admin-btn-danger">Delete user</button>
                </form>
            </div>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>