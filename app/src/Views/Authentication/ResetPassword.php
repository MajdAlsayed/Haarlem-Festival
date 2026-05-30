<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset password</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/auth.css">
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

<script>
(function () {
    document.querySelectorAll('.auth-eye-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(this.getAttribute('data-target'));
            if (input) input.type = input.type === 'password' ? 'text' : 'password';
        });
    });

    function showErr(input, msg) {
        var field = input.closest('.auth-field');
        if (!field) return;
        var err = field.querySelector('.auth-field-error');
        if (!err) {
            err = document.createElement('span');
            err.className = 'auth-field-error';
            field.appendChild(err);
        }
        err.textContent = msg;
        input.classList.toggle('auth-input-invalid', msg !== '');
    }

    function strongPassword(p) {
        return p.length >= 12 && /[a-z]/.test(p) && /[A-Z]/.test(p) && /\d/.test(p) && /[^a-zA-Z0-9]/.test(p);
    }

    var pw  = document.getElementById('password');
    var pwc = document.getElementById('password_confirm');
    var form = document.querySelector('form');

    if (!form) return;

    [pw, pwc].forEach(function (el) {
        if (el) el.addEventListener('input', function () { showErr(this, ''); });
    });

    form.addEventListener('submit', function (e) {
        var ok = true;

        if (!pw.value) { showErr(pw, 'Password is required.'); ok = false; }
        else if (!strongPassword(pw.value)) { showErr(pw, 'Password must be 12+ characters with uppercase, lowercase, number and symbol.'); ok = false; }
        else showErr(pw, '');

        if (!pwc.value) { showErr(pwc, 'Please confirm your password.'); ok = false; }
        else if (pwc.value !== pw.value) { showErr(pwc, 'Passwords do not match.'); ok = false; }
        else showErr(pwc, '');

        if (!ok) e.preventDefault();
    });
})();
</script>

</body>
</html>