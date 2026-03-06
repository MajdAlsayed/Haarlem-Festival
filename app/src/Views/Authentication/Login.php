<?php
$app = (new \App\Repositories\SettingsRepository())->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login — <?= htmlspecialchars($app['site_name']) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version']) ?>">
    <link rel="stylesheet" href="/css/auth.css?v=<?= htmlspecialchars($app['css_version']) ?>">
</head>
<body class="auth-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <section class="auth-section">
        <div class="auth-card">

            <h1 class="auth-title">Login</h1>

            <?php if ($viewModel->error !== null): ?>
                <div class="auth-error">
                    <?= htmlspecialchars($viewModel->error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/login" autocomplete="on" novalidate>
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

                <div class="auth-field">
                <label for="identifier">Email or username</label>
                <input type="text" id="identifier" name="identifier" required autofocus autocomplete="username">
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="auth-input-wrap">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="auth-eye-btn" data-target="password" aria-label="Toggle password visibility">
                            <img src="/images/eye.jpg" class="eye-icon" alt="Show password">
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">Login</button>
            </form>
            <p class="auth-switch">
                Forgot your password? <a href="/forgot-password">Reset your password</a>
            </p>
                
            <p class="auth-switch">
                No account? <a href="/register">Register here</a>
            </p>

        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
(function () {
    document.querySelectorAll('.auth-eye-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = this.getAttribute('data-target');
            var input    = document.getElementById(targetId);
            var img      = this.querySelector('.eye-icon');

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                img.src    = '/images/eye.jpg';
            } else {
                input.type = 'password';
                img.src    = '/images/eye.jpg';
            }
        });
    });
})();
</script>

</body>
</html>