<?php
/** @var \App\ViewModels\AdminOrdersListViewModel $viewModel */
$app = $viewModel->appSettings;
$h = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$pageTitle = 'View orders — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page admin-orders-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--wide">
        <nav class="admin-breadcrumb" aria-label="Breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Orders</span>
        </nav>

        <section class="admin-page-header admin-page-header-row">
            <div>
                <h1 class="admin-title">View orders</h1>
                <p class="admin-subtitle">Browse festival orders, open order details, and export data.</p>
            </div>
            <div class="admin-toolbar-actions">
                <a href="/admin/orders/export" class="admin-btn admin-btn-primary admin-btn-export">
                    <span class="admin-btn-icon" aria-hidden="true">↓</span>
                    Export orders
                </a>
            </div>
        </section>

        <div class="admin-stats admin-stats--orders">
            <div class="admin-stat-card">
                <span class="admin-stat-label">Total orders</span>
                <strong class="admin-stat-value"><?= $h((string) $viewModel->totalCount()) ?></strong>
            </div>
            <div class="admin-stat-card">
                <span class="admin-stat-label">Paid</span>
                <strong class="admin-stat-value admin-stat-value--ok"><?= $h((string) $viewModel->countByStatus('paid')) ?></strong>
            </div>
            <div class="admin-stat-card">
                <span class="admin-stat-label">Pending</span>
                <strong class="admin-stat-value admin-stat-value--warn"><?= $h((string) $viewModel->countByStatus('pending')) ?></strong>
            </div>
            <div class="admin-stat-card">
                <span class="admin-stat-label">Paid revenue</span>
                <strong class="admin-stat-value"><?= $h($viewModel->formatMoney($viewModel->revenueTotal())) ?></strong>
            </div>
        </div>

        <section class="admin-panel admin-orders-panel">
            <?php if ($viewModel->orders === []): ?>
                <div class="admin-empty-state">
                    <p class="admin-empty-title">No orders yet</p>
                    <p class="admin-empty-text">Orders will appear here once customers complete checkout.</p>
                </div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table admin-orders-table">
                        <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Created</th>
                            <th>Items</th>
                            <th class="admin-table-actions-head">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($viewModel->orders as $row): ?>
                            <?php
                            $oid = (int) ($row['order_id'] ?? 0);
                            $status = (string) ($row['status'] ?? '');
                            $name = trim(($row['customer_first_name'] ?? '') . ' ' . ($row['customer_last_name'] ?? ''));
                            ?>
                            <tr>
                                <td>
                                    <a href="/admin/orders/view?order_id=<?= $h((string) $oid) ?>" class="admin-order-id-link">
                                        #<?= $h((string) $oid) ?>
                                    </a>
                                </td>
                                <td class="admin-user-name-cell">
                                    <div class="admin-user-name"><?= $h($name !== '' ? $name : '—') ?></div>
                                    <div class="admin-user-email"><?= $h((string) ($row['customer_email'] ?? '')) ?></div>
                                </td>
                                <td>
                                    <span class="<?= $h($viewModel->statusBadgeClass($status)) ?>"><?= $h($status) ?></span>
                                </td>
                                <td class="admin-money-cell"><?= $h($viewModel->formatMoney($row['total_amount'] ?? 0)) ?></td>
                                <td class="admin-date-cell"><?= $h((string) ($row['paid_at'] ?? '—')) ?></td>
                                <td class="admin-date-cell"><?= $h((string) ($row['created_at'] ?? '')) ?></td>
                                <td><?= $h((string) ($row['line_items_count'] ?? '0')) ?></td>
                                <td class="admin-table-actions">
                                    <a href="/admin/orders/view?order_id=<?= $h((string) $oid) ?>" class="admin-btn admin-btn-secondary admin-btn-sm">View</a>
                                    <a href="/admin/orders/tickets?order_id=<?= $h((string) $oid) ?>" class="admin-btn admin-btn-secondary admin-btn-sm">Tickets</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
