<?php
/** @var \App\ViewModels\AdminOrdersListViewModel $viewModel */
$app = $viewModel->appSettings;

$pageTitle = 'View orders — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--wide">
        <nav class="admin-breadcrumb">
            <a href="/"><?= htmlspecialchars((string)($app['site_name'] ?? 'Festival')) ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Orders</span>
        </nav>

        <h1 class="admin-title">View orders</h1>
        <p class="admin-lead">
            Read-only list of all orders. Use <a href="/admin/orders/export">Export orders</a> to download CSV or Excel.
        </p>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Paid at</th>
                    <th>Created</th>
                    <th>Lines</th>
                    <th>Tickets</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($viewModel->orders as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($row['order_id'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['user_id'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['customer_email'] ?? '')) ?></td>
                        <td><?= htmlspecialchars(trim(($row['customer_first_name'] ?? '') . ' ' . ($row['customer_last_name'] ?? ''))) ?></td>
                        <td><?= htmlspecialchars((string)($row['status'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['total_amount'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['paid_at'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['created_at'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['line_items_count'] ?? '')) ?></td>
                        <td>
                            <a href="/admin/orders/tickets?order_id=<?= htmlspecialchars((string)($row['order_id'] ?? '0')) ?>">
                                Codes
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>