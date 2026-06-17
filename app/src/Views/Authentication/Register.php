<?php

// Settings for the page title, styles, body class
$pageTitle = 'Register — ' . ('Haarlem Festival');
$pageStyles = ['/css/pages/auth.css'];
$bodyClass = 'auth-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Register', 'url' => null],
];
?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

<main>
    <section class="auth-section">
        <div class="festival-card auth-card">

            <h1 class="section-title section-title--accent auth-title">Create account</h1>

            <?php if ($viewModel->error !== null): ?>
                <div class="auth-error">
                    <?= htmlspecialchars($viewModel->error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/register" autocomplete="on" novalidate>
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

                <div class="auth-field">
                    <label class="copy-text copy-text--sm" for="username">Username</label>
                    <input type="text" id="username" name="username" required maxlength="50"
                           value="<?= htmlspecialchars($viewModel->username) ?>" autofocus>
                </div>

                <div class="auth-field">
                    <label class="copy-text copy-text--sm" for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($viewModel->email) ?>">
                </div>

                <div class="auth-field-row">
                    <div class="auth-field">
                        <label class="copy-text copy-text--sm" for="first_name">First name</label>
                        <input type="text" id="first_name" name="first_name" required
                               value="<?= htmlspecialchars($viewModel->firstName) ?>">
                    </div>

                    <div class="auth-field">
                        <label class="copy-text copy-text--sm" for="last_name">Last name</label>
                        <input type="text" id="last_name" name="last_name" required
                               value="<?= htmlspecialchars($viewModel->lastName) ?>">
                    </div>
                </div>

                <div class="auth-field">
                    <label class="copy-text copy-text--sm" for="password">Password</label>
                    <div class="auth-input-wrap">
                        <input type="password" id="password" name="password" required autocomplete="new-password">
                        <button type="button" class="auth-eye-btn" data-target="password" aria-label="Toggle password visibility">
                            <img src="/images/eye.jpg" class="eye-icon" alt="">
                        </button>
                    </div>
                    <small class="copy-text copy-text--sm copy-text--muted auth-hint">
                        12+ characters with uppercase, lowercase, number and symbol.
                    </small>
                </div>

                <div class="auth-field">
                    <label class="copy-text copy-text--sm" for="password_confirm">Confirm password</label>
                    <div class="auth-input-wrap">
                        <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">
                        <button type="button" class="auth-eye-btn" data-target="password_confirm" aria-label="Toggle password visibility">
                            <img src="/images/eye.jpg" class="eye-icon" alt="">
                        </button>
                    </div>
                </div>

                <div class="auth-field auth-captcha">
                    <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($viewModel->recaptchaSiteKey) ?>"></div>
                </div>

                <button type="submit" class="btn btn--primary auth-btn">Create account</button>
            </form>

            <p class="copy-text copy-text--sm auth-switch">
                Already have an account? <a href="/login">Login here</a>
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