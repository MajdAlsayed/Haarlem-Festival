<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
$currentPath = rtrim($currentPath, '/') ?: '/';
if (!isset($navLinks)) $navLinks = (new \App\Repositories\MenuRepository())->getNavLinks();
if (!isset($app)) $app = (new \App\Repositories\SettingsRepository())->getAll();
$isLoggedIn = !empty($_SESSION['auth'] ?? []);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['auth']['username'] ?? '') : '';
$isAdmin = \App\Core\AdminAuth::isAdmin();
$canUseTicketScanner = $isLoggedIn && \App\Core\TicketScannerAuth::currentUserCanScan();
$adminNavActive = $isAdmin && strpos($currentPath, '/admin') === 0 && $currentPath !== '/admin/scan';
$scannerNavActive = $currentPath === '/admin/scan';
$cartBadgeCount = 0;
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars((string) ($app['css_version'] ?? '1')) ?>">

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
                <span id="cartBadge" class="cart-badge"><?= (int) $cartBadgeCount ?></span>
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
                        <li>
                            <a class="dropdown-item<?= str_starts_with($currentPath, '/account') ? ' active' : '' ?>" href="/account/orders">My orders</a>
                        </li>
                        <li>
                            <a class="dropdown-item<?= str_starts_with($currentPath, '/my-program') ? ' active' : '' ?>" href="/my-program">My program</a>
                        </li>
                        <?php if ($isAdmin): ?>
                            <li>
                                <a class="dropdown-item<?= $adminNavActive ? ' active' : '' ?>" href="/admin">Admin dashboard</a>
                            </li>
                        <?php endif; ?>
                        <?php if ($canUseTicketScanner): ?>
                            <li>
                                <a class="dropdown-item<?= $scannerNavActive ? ' active' : '' ?>" href="/admin/scan">Scan tickets</a>
                            </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
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