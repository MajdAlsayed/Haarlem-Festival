<?php
$app = $viewModel->appSettings;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register — <?= htmlspecialchars($app['site_name']) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version']) ?>">
    <link rel="stylesheet" href="/css/auth.css?v=<?= htmlspecialchars($app['css_version']) ?>">
</head>
<body class="auth-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <section class="auth-section">
        <div class="auth-card">

            <h1 class="auth-title">Create account</h1>

            <?php if ($viewModel->error !== null): ?>
                <div class="auth-error">
                    <?= htmlspecialchars($viewModel->error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/register" autocomplete="on" novalidate>
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

                <div class="auth-field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required maxlength="50"
                           value="<?= htmlspecialchars($viewModel->username) ?>" autofocus>
                </div>

                <div class="auth-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($viewModel->email) ?>">
                </div>

                <div class="auth-field-row">
                    <div class="auth-field">
                        <label for="first_name">First name</label>
                        <input type="text" id="first_name" name="first_name" required
                               value="<?= htmlspecialchars($viewModel->firstName) ?>">
                    </div>
                    <div class="auth-field">
                        <label for="last_name">Last name</label>
                        <input type="text" id="last_name" name="last_name" required
                               value="<?= htmlspecialchars($viewModel->lastName) ?>">
                    </div>
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="auth-input-wrap">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="auth-eye-btn" data-target="password" aria-label="Toggle password visibility">
                            <img src="/images/eye.jpg" class="eye-icon" alt="Show password">
                        </button>
                    </div>
                    <small class="auth-hint">
                        12+ characters with uppercase, lowercase, number and symbol.
                    </small>
                </div>

                <div class="auth-field">
                    <label for="password_confirm">Confirm password</label>
                    <div class="auth-input-wrap">
                        <input type="password" id="password_confirm" name="password_confirm" required>
                        <button type="button" class="auth-eye-btn" data-target="password_confirm" aria-label="Toggle password visibility">
                            <img src="/images/eye.jpg" class="eye-icon" alt="Show password">
                        </button>
                    </div>
                </div>

                <div class="auth-field auth-captcha">
                    <label for="captcha"><?= htmlspecialchars($viewModel->captchaQuestion) ?></label>
                    <input type="text" id="captcha" name="captcha" required maxlength="4"
                           autocomplete="off" inputmode="numeric">
                </div>

                <button type="submit" class="btn btn-primary auth-btn">Create account</button>
            </form>

            <p class="auth-switch">
                Already have an account? <a href="/login">Login here</a>
            </p>

        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>



</body>
</html>