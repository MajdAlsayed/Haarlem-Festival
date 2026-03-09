<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
$currentPath = rtrim($currentPath, '/') ?: '/';
if (!isset($navLinks)) $navLinks = (new \App\Repositories\MenuRepository())->getNavLinks();
if (!isset($app)) $app = (new \App\Repositories\SettingsRepository())->getAll();
$isLoggedIn = !empty($_SESSION['auth'] ?? []);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['auth']['username'] ?? '') : '';
?>
<header>
    <div class="nav-container">

        <div class="logo">
            <a href="<?= htmlspecialchars($app['home_path']) ?>" class="logo-link">
                <img src="<?= htmlspecialchars($app['logo_src'] ?? $app['icons_path'] . $app['logo_filename']) ?>"
                     alt="<?= htmlspecialchars($app['site_name']) ?>"
                     class="logo-img">
            </a>
        </div>

        <nav class="nav-menu">
            <?php foreach ($navLinks as $link): ?>
                <?php
                $isActive = $currentPath === $link['path']
                    || ($link['path'] === '/' && in_array($currentPath, [$app['home_path'] ?? '/', '/home'], true))
                    || ($link['path'] !== '/' && $link['path'] !== '' && (strpos($currentPath, $link['path'] . '/') === 0));
                ?>
                <a href="<?= htmlspecialchars($link['path']) ?>" class="nav-link<?= $isActive ? ' active' : '' ?>"><?= htmlspecialchars($link['label']) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="nav-actions">

            <button class="icon-btn search-btn" type="button" aria-label="Search">🔍</button>

            <div class="language-selector" role="button" tabindex="0" aria-label="Language selector">
                <span>EN</span>
                <span class="dropdown">▼</span>
            </div>

            <!-- Cart button (opens drawer) -->
            <button id="cartOpenBtn" class="icon-btn cart-btn" type="button" aria-label="Open cart">
                🛒
                <span id="cartBadge" class="cart-badge">0</span>
            </button>

            <button type="button" class="icon-btn search-btn" aria-label="Search">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </button>

<?php if ($isLoggedIn): ?>
                <div class="nav-user">
                    <span class="nav-username">👤 <?= $username ?></span>
                    <a href="/logout" class="btn btn-outline nav-auth-btn">Logout</a>
                </div>
            <?php else: ?>
                <div class="nav-user">
                    <a href="/login" class="btn btn-outline nav-auth-btn">Login</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</header>