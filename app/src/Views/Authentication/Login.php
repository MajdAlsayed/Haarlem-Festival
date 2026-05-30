<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/auth.css">
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
                <?php if (!empty($viewModel->returnTo)): ?>
                    <input type="hidden" name="return" value="<?= htmlspecialchars($viewModel->returnTo) ?>">
                <?php endif; ?>

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

    var id = document.getElementById('identifier');
    var pw = document.getElementById('password');

    [id, pw].forEach(function (el) {
        if (el) el.addEventListener('input', function () { showErr(this, ''); });
    });

    document.querySelector('form').addEventListener('submit', function (e) {
        var ok = true;
        if (!id.value.trim()) { showErr(id, 'Please enter your email or username.'); ok = false; } else showErr(id, '');
        if (!pw.value)        { showErr(pw, 'Please enter your password.');           ok = false; } else showErr(pw, '');
        if (!ok) e.preventDefault();
    });
})();
</script>

</body>
</html>