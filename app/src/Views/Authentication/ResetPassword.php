<?php
$app = $viewModel->appSettings;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset password — <?= htmlspecialchars($app['site_name']) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version']) ?>">
    <link rel="stylesheet" href="/css/auth.css?v=<?= htmlspecialchars($app['css_version']) ?>">
</head>
<body class="auth-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <section class="auth-section">
        <div class="auth-card">
            <h1 class="auth-title">Reset password</h1>

            <?php if ($viewModel->error !== null): ?>
                <div class="auth-error"><?= htmlspecialchars($viewModel->error) ?></div>
            <?php endif; ?>

            <?php if ($viewModel->success !== null): ?>
                <div class="auth-success"><?= htmlspecialchars($viewModel->success) ?></div>
            <?php endif; ?>

            <?php if ($viewModel->success === null): ?>
                <form method="post" action="/reset-password" autocomplete="off" novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($viewModel->token) ?>">

                    <div class="auth-field">
                        <label for="password">New password</label>
                        <div class="auth-input-wrap">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="auth-eye-btn" data-target="password" aria-label="Toggle password visibility">
                                <img src="/images/eye.jpg" class="eye-icon" alt="Show password">
                            </button>
                        </div>
                        <small class="auth-hint">12+ characters with uppercase, lowercase, number and symbol.</small>
                    </div>

                    <div class="auth-field">
                        <label for="password_confirm">Confirm new password</label>
                        <div class="auth-input-wrap">
                            <input type="password" id="password_confirm" name="password_confirm" required>
                            <button type="button" class="auth-eye-btn" data-target="password_confirm" aria-label="Toggle password visibility">
                                <img src="/images/eye.jpg" class="eye-icon" alt="Show password">
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary auth-btn">Save new password</button>
                </form>
            <?php else: ?>
                <p class="auth-switch"><a href="/login">Go to login</a></p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>



</body>
</html>