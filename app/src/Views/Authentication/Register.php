<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/auth.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
                    <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($viewModel->recaptchaSiteKey) ?>"></div>
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

    var fields = ['username', 'email', 'first_name', 'last_name', 'password', 'password_confirm'];
    fields.forEach(function (name) {
        var el = document.getElementById(name);
        if (el) el.addEventListener('input', function () { showErr(this, ''); });
    });

    document.querySelector('form').addEventListener('submit', function (e) {
        var username = document.getElementById('username');
        var email    = document.getElementById('email');
        var firstName = document.getElementById('first_name');
        var lastName  = document.getElementById('last_name');
        var pw  = document.getElementById('password');
        var pwc = document.getElementById('password_confirm');
        var ok  = true;

        if (!username.value.trim()) { showErr(username, 'Username is required.'); ok = false; } else showErr(username, '');

        var ev = email.value.trim();
        if (!ev) { showErr(email, 'Email is required.'); ok = false; }
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(ev)) { showErr(email, 'Please enter a valid email address.'); ok = false; }
        else showErr(email, '');

        if (!firstName.value.trim()) { showErr(firstName, 'First name is required.'); ok = false; } else showErr(firstName, '');
        if (!lastName.value.trim())  { showErr(lastName,  'Last name is required.');  ok = false; } else showErr(lastName,  '');

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