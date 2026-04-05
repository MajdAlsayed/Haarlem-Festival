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
    <span aria-hidden="true"> · </span>
    <a href="<?= htmlspecialchars($base) ?>/cms/history">Edit History page</a>
    <span aria-hidden="true"> · </span>
    <a href="<?= htmlspecialchars($base) ?>/cms/history-locations">Edit History Locations page</a>
    <span aria-hidden="true"> · </span>
    <a href="<?= htmlspecialchars($base) ?>/cms/history-tours">Edit History Tours page</a>
    <span aria-hidden="true"> · </span>
    <a href="<?= htmlspecialchars($base) ?>/cms/history/location/st-bavo">Edit St. Bavo</a>
    <span aria-hidden="true"> · </span>
    <a href="<?= htmlspecialchars($base) ?>/cms/history/location/grote-markt">Edit Grote Markt</a>
</nav>
