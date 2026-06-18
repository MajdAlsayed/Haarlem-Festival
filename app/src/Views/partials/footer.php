<?php if (!isset($app)) $app = (new \App\Repositories\SettingsRepository())->getAll(); ?>
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-logo">
            <img src="<?= htmlspecialchars($app['logo_src'] ?? $app['icons_path'] . $app['logo_filename']) ?>" alt="<?= htmlspecialchars($app['site_name']) ?>" class="footer-logo-img">
        </div>

        <nav class="footer-nav" aria-label="Footer navigation">
            <div class="footer-column">
                <h3 class="footer-heading"><?= strtoupper(htmlspecialchars($app['site_name'])) ?></h3>
                <ul>
                    <li><a href="#">Festival Program</a></li>
                    <li><a href="#">Tickets &amp; Packages</a></li>
                    <li><a href="#">Locations</a></li>
                    <li><a href="#">Frequently Asked Questions</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3 class="footer-heading">EXPLORE HAARLEM</h3>
                <ul>
                    <li><a href="#">About Haarlem</a></li>
                    <li><a href="#">Historic Landmarks</a></li>
                    <li><a href="#">Food &amp; Drinks</a></li>
                    <li><a href="#">City Tours</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3 class="footer-heading">EXPERIENCES</h3>
                <ul>
                    <li><a href="/jazz">Jazz</a></li>
                    <li><a href="/dance">Dance</a></li>
                    <li><a href="/dance#dance-artists">Dance artists</a></li>
                    <li><a href="/history">History</a></li>
                    <li><a href="/food">Food</a></li>
                    <li><a href="/stories">Stories</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3 class="footer-heading">CONTACT</h3>
                <ul>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Partners</a></li>
                    <li><a href="#">Press</a></li>
                </ul>
            </div>
            <div class="footer-column footer-column-social">
                <h3 class="footer-heading">FOLLOW US</h3>
                <div class="footer-social">
                    <?php foreach (($app['footer']['social_icons'] ?? []) as $icon): ?>
                    <a href="#" class="footer-social-link" aria-label="<?= htmlspecialchars(pathinfo($icon, PATHINFO_FILENAME)) ?>"><img src="<?= htmlspecialchars($app['icons_path']) ?><?= rawurlencode($icon) ?>" alt="" class="footer-social-icon-img"></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </nav>

        <div class="footer-app">
            <p class="footer-app-title">GET THE APP</p>
            <div class="footer-app-buttons">
                <?php foreach (($app['footer']['app_icons'] ?? []) as $i => $icon): $label = ($app['footer']['app_labels'] ?? [])[$i] ?? ''; ?>
                <a href="#" class="footer-app-btn"><img src="<?= htmlspecialchars($app['icons_path']) ?><?= rawurlencode($icon) ?>" alt="<?= htmlspecialchars($label) ?>" class="footer-app-badge"></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="footer-bottom">
            <span class="footer-copy">&copy; <?= date('Y') ?> <?= htmlspecialchars($app['site_name']) ?></span>
            <span class="footer-dot">|</span>
            <a href="#" class="footer-legal">Privacy</a>
            <span class="footer-dot">|</span>
            <a href="#" class="footer-legal">Terms</a>
        </div>
    </div>
</footer>

<?php include __DIR__ . '/cartDrawer.php'; ?>
<?php
$cartCsrf = \App\Core\Csrf::peek('cart') ?? \App\Core\Csrf::token('cart');
?>
<script>
window.__CSRF_CART__ = <?= json_encode($cartCsrf, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/cart.js?v=1" defer></script>
<script src="/js/navMenu.js?v=1" defer></script>
