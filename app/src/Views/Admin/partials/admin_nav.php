<?php
/** @var array $app */
$base = '/admin';
?>
<nav class="admin-cms-nav" aria-label="Admin CMS">
    <a href="<?= htmlspecialchars($base) ?>/orders">View orders</a>
    <span aria-hidden="true"> · </span>
    <a href="<?= htmlspecialchars($base) ?>/orders/export">Export orders</a>
    <span aria-hidden="true"> · </span>
    <a href="<?= htmlspecialchars($base) ?>/scan">Scan tickets</a>
    <span aria-hidden="true"> · </span>
    <a href="<?= htmlspecialchars($base) ?>/cms/homepage">Edit homepage</a>
    <span aria-hidden="true"> · </span>
    <a href="<?= htmlspecialchars($base) ?>/cms/dance">Edit Dance page</a>
</nav>
