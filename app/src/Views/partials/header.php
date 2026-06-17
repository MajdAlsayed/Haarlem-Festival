<?php
// Get the current page path for active menu links
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$currentPath = rtrim($currentPath, '/') ?: '/';

// Load menu links and site settings if they were not passed to the view
if (!isset($navLinks)) {
    $navLinks = (new \App\Repositories\MenuRepository())->getNavLinks();
}
if (!isset($app)) {
    $app = (new \App\Repositories\SettingsRepository())->getAll();
}

// Check the current user state
$isLoggedIn = !empty($_SESSION['auth'] ?? []);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['auth']['username'] ?? '') : '';

$isAdmin = \App\Core\AdminAuth::isAdmin();
$canUseTicketScanner = $isLoggedIn && \App\Core\TicketScannerAuth::currentUserCanScan();

$adminNavActive = $isAdmin
        && strpos($currentPath, '/admin') === 0
        && $currentPath !== '/admin/scan';

$scannerNavActive = $currentPath === '/admin/scan';
$cartBadgeCount = 0;

// Checks if a navigation link should be marked as active
$isNavLinkActive = function (array $link) use ($currentPath, $app): bool {
    $linkPath = $link['path'] ?? '';

    if ($currentPath === $linkPath) {
        return true;
    }
    if ($linkPath === '/') {
        return in_array($currentPath, [$app['home_path'] ?? '/', '/home'], true);
    }

    return $linkPath !== '' && strpos($currentPath, $linkPath . '/') === 0;
};
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

        <button
                class="nav-toggle"
                type="button"
                aria-label="Toggle navigation"
                aria-expanded="false"
                aria-controls="siteNavMenu"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="nav-menu" id="siteNavMenu">
            <?php foreach ($navLinks as $link): ?>
                <?php $isActive = $isNavLinkActive($link); ?>
                <a href="<?= htmlspecialchars($link['path']) ?>"
                   class="nav-link<?= $isActive ? ' active' : '' ?>">
                    <?= htmlspecialchars($link['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="nav-actions">

            <button class="icon-btn search-btn" type="button" aria-label="Search">🔍</button>
            <div class="language-selector" role="button" tabindex="0" aria-label="Language selector">
                <span>EN</span>
                <span class="dropdown">▼</span>
            </div>

            <button
                    id="cartOpenBtn"
                    class="icon-btn cart-btn"
                    type="button"
                    aria-label="Open cart"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#cartOffcanvas"
                    aria-controls="cartOffcanvas"
            >
                🛒
                <span id="cartBadge" class="cart-badge"><?= (int)$cartBadgeCount ?></span>
            </button>

            <?php if ($isLoggedIn): ?>
                <div class="nav-user nav-user-dropdown dropdown">
                    <button
                            class="btn btn-outline nav-auth-btn nav-user-menu-toggle dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="true"
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="Account menu"
                            id="navUserMenuBtn"
                    >
                        <?= $isAdmin ? '👤' : ($canUseTicketScanner ? '🎫' : '👤') ?>
                        <span class="nav-user-menu-label"><?= $username ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end nav-user-menu" aria-labelledby="navUserMenuBtn">
                        <?php if (!$isAdmin): ?>
                            <li>
                                <a class="dropdown-item<?= str_starts_with($currentPath, '/account') ? ' active' : '' ?>"
                                   href="/account/orders">
                                    My orders
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item<?= str_starts_with($currentPath, '/my-program') ? ' active' : '' ?>"
                                   href="/my-program">
                                    My program
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($isAdmin): ?>
                            <li>
                                <a class="dropdown-item<?= $adminNavActive ? ' active' : '' ?>" href="/admin">
                                    Admin dashboard
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($canUseTicketScanner): ?>
                            <li>
                                <a class="dropdown-item<?= $scannerNavActive ? ' active' : '' ?>" href="/admin/scan">
                                    Scan tickets
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item nav-user-menu-logout" href="/logout">Logout</a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="nav-user">
                    <a href="/login" class="btn btn-outline nav-auth-btn">Login</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</header>