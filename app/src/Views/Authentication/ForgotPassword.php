<?php

// Settings for the page title, styles, body class
$pageTitle = 'Forgot password — ' . ('Haarlem Festival');
$pageStyles = ['/css/pages/auth.css'];
$bodyClass = 'auth-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Forgot password', 'url' => null],
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
            <h1 class="section-title section-title--accent auth-title">Forgot password</h1>

            <p class="copy-text auth-copy">
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
                    <strong class="section-subtitle">Dummy reset link</strong>
                    <a class="copy-text copy-text--sm auth-dummy-link" href="<?= htmlspecialchars($viewModel->dummyLink) ?>">
                        <?= htmlspecialchars($viewModel->dummyLink) ?>
                    </a>
                    <small class="copy-text copy-text--sm copy-text--muted auth-hint">
                        This link expires automatically after 60 minutes.
                    </small>
                </div>
            <?php endif; ?>

            <form method="post" action="/forgot-password" autocomplete="on" novalidate>
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

                <div class="auth-field">
                    <label class="copy-text copy-text--sm" for="identifier">Email or username</label>
                    <input
                            type="text"
                            id="identifier"
                            name="identifier"
                            required
                            autofocus
                            value="<?= htmlspecialchars($viewModel->identifier) ?>"
                    >
                </div>

                <button type="submit" class="btn btn--primary auth-btn">Send reset link</button>
            </form>

            <p class="copy-text copy-text--sm auth-switch">
                Remembered it? <a href="/login">Back to login</a>
            </p>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>