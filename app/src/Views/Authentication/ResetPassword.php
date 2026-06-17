<?php

// Settings for the page title, styles, body class
$pageTitle = 'Reset password — ' . ('Haarlem Festival');
$pageStyles = ['/css/pages/auth.css'];
$bodyClass = 'auth-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Reset password', 'url' => null],
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
            <h1 class="section-title section-title--accent auth-title">Reset password</h1>

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
                        <label class="copy-text copy-text--sm" for="password">New password</label>
                        <div class="auth-input-wrap">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="auth-eye-btn" data-target="password" aria-label="Toggle password visibility">
                                <img src="/images/eye.jpg" class="eye-icon" alt="">
                            </button>
                        </div>
                        <small class="copy-text copy-text--sm copy-text--muted auth-hint">
                            12+ characters with uppercase, lowercase, number and symbol.
                        </small>
                    </div>

                    <div class="auth-field">
                        <label class="copy-text copy-text--sm" for="password_confirm">Confirm new password</label>
                        <div class="auth-input-wrap">
                            <input type="password" id="password_confirm" name="password_confirm" required>
                            <button type="button" class="auth-eye-btn" data-target="password_confirm" aria-label="Toggle password visibility">
                                <img src="/images/eye.jpg" class="eye-icon" alt="">
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn--primary auth-btn">Save new password</button>
                </form>
            <?php else: ?>
                <p class="copy-text copy-text--sm auth-switch">
                    <a href="/login">Go to login</a>
                </p>
            <?php endif; ?>
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