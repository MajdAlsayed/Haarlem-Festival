<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
if (!isset($navLinks)) $navLinks = (new \App\Repositories\MenuRepository())->getNavLinks();
if (!isset($app)) $app = (new \App\Repositories\SettingsRepository())->getAll();
$isLoggedIn = !empty($_SESSION['auth'] ?? []);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['auth']['username'] ?? '') : '';
?>
<header>
    <div class="nav-container">

        <div class="logo">
            <a href="<?= htmlspecialchars($app['home_path']) ?>" class="logo-link">
                <img src="<?= htmlspecialchars($app['icons_path']) ?><?= rawurlencode($app['logo_filename']) ?>"
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

            <button type="button" class="icon-btn search-btn" aria-label="Search">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
            </button>

            <div class="language-selector" role="button" tabindex="0" aria-label="Language">
                <span>EN</span>
                <span class="dropdown">▼</span>
            </div>

            <button type="button" class="icon-btn cart-btn" aria-label="Cart">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <span class="cart-badge">0</span>
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