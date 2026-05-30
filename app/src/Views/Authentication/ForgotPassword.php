<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot password</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/auth.css">
</head>
<body class="auth-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <section class="auth-section">
        <div class="auth-card">
            <h1 class="auth-title">Forgot password</h1>

            <p class="auth-copy">
                Enter your email address or username. If the account exists, a reset link will be prepared.
                For this project, the link is shown on screen instead of being emailed.
            </p>

            <?php if ($viewModel->error !== null): ?>
                <div class="auth-error"><?= htmlspecialchars($viewModel->error) ?></div>
            <?php endif; ?>

            <?php if ($viewModel->success !== null): ?>
                <div class="auth-success"><?= htmlspecialchars($viewModel->success) ?></div>
            <?php endif; ?>

            <?php if ($viewModel->dummyLink !== null): ?>
                <div class="auth-info-box">
                    <strong>Dummy reset link</strong>
                    <a class="auth-dummy-link" href="<?= htmlspecialchars($viewModel->dummyLink) ?>">
                        <?= htmlspecialchars($viewModel->dummyLink) ?>
                    </a>
                    <small class="auth-hint">This link expires automatically after 60 minutes.</small>
                </div>
            <?php endif; ?>

            <form method="post" action="/forgot-password" autocomplete="on" novalidate>
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

                <div class="auth-field">
                    <label for="identifier">Email or username</label>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        required
                        autofocus
                        value="<?= htmlspecialchars($viewModel->identifier) ?>"
                    >
                </div>

                <button type="submit" class="btn btn-primary auth-btn">Send reset link</button>
            </form>

            <p class="auth-switch">
                Remembered it? <a href="/login">Back to login</a>
            </p>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
(function () {
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
    if (id) id.addEventListener('input', function () { showErr(this, ''); });

    document.querySelector('form').addEventListener('submit', function (e) {
        if (!id.value.trim()) {
            showErr(id, 'Please enter your email or username.');
            e.preventDefault();
        } else {
            showErr(id, '');
        }
    });
})();
</script>

</body>
</html>