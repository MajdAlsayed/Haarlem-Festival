<?php
$app = $viewModel->appSettings;

// Settings for the page title, styles, body class
$pageTitle = 'Login — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/pages/auth.css'];
$bodyClass = 'auth-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Login', 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

<main>
    <section class="auth-section">
        <div class="festival-card auth-card">

            <h1 class="section-title section-title--accent section-title--underlined auth-title">Login</h1>

            <?php if ($viewModel->error !== null): ?>
                <div class="auth-error">
                    <?= htmlspecialchars($viewModel->error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/login" autocomplete="on" novalidate>
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

                <?php if (!empty($viewModel->returnTo)): ?>
                    <input type="hidden" name="return" value="<?= htmlspecialchars($viewModel->returnTo) ?>">
                <?php endif; ?>

                <div class="auth-field">
                    <label class="copy-text copy-text--sm" for="identifier">Email or username</label>
                    <input type="text" id="identifier" name="identifier" required autofocus autocomplete="username">
                </div>

                <div class="auth-field">
                    <label class="copy-text copy-text--sm" for="password">Password</label>
                    <div class="auth-input-wrap">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <button type="button" class="auth-eye-btn" data-target="password" aria-label="Toggle password visibility">
                            <img src="/images/eye.jpg" class="eye-icon" alt="">
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn--primary auth-btn">Login</button>
            </form>

            <p class="copy-text copy-text--sm auth-switch">
                Forgot your password? <a href="/forgot-password">Reset your password</a>
            </p>

            <p class="copy-text copy-text--sm auth-switch">
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
                var input = document.getElementById(targetId);

                if (!input) return;

                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });
    })();
</script>

</body>
</html>