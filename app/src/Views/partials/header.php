<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
$navLinks = (new \App\Repositories\MenuRepository())->getNavLinks();
$app = (new \App\Repositories\SettingsRepository())->getAll();
?>
<header>
    <div class="nav-container">
        <div class="logo">
            <a href="<?= htmlspecialchars($app['home_path']) ?>" class="logo-link"><img src="<?= htmlspecialchars($app['icons_path']) ?><?= rawurlencode($app['logo_filename']) ?>" alt="<?= htmlspecialchars($app['site_name']) ?>" class="logo-img"></a>
        </div>
        <nav class="nav-menu">
            <?php foreach ($navLinks as $link): ?>
                <a href="<?= htmlspecialchars($link['path']) ?>" class="nav-link<?= ($currentPath === $link['path'] || ($link['path'] === '/' && ($currentPath === $app['home_path'] || $currentPath === '/home'))) ? ' active' : '' ?>"><?= htmlspecialchars($link['label']) ?></a><?php // active class = current page, highlighted in CSS ?>
            <?php endforeach; ?>
        </nav>
        <div class="nav-actions">
            <?php // Search + cart: inline SVG. viewBox, path d=, cx/cy are drawing coords — they render the icon shape, not as text ?>
            <button type="button" class="icon-btn search-btn" aria-label="Search">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </button>
            <div class="language-selector" role="button" tabindex="0" aria-label="Language">
                <span>EN</span>
                <span class="dropdown">▼</span>
            </div>
            <button type="button" class="icon-btn cart-btn" aria-label="Cart">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span class="cart-badge">0</span>
            </button>
        </div>
    </div>
</header>
