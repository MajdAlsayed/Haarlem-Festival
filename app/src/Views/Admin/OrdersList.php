<?php
/** @var \App\ViewModels\AdminOrdersListViewModel $viewModel */
$app = $viewModel->appSettings;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View orders — <?= htmlspecialchars((string)($app['site_name'] ?? 'Haarlem Festival')) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars((string)($app['css_version'] ?? '1.0')) ?>">
    <style>
        .admin-cms-wrap { max-width: 1100px; margin: 1rem auto; padding: 0 1rem; }
        .admin-cms-nav { margin: 0.75rem 0 1rem; font-size: 0.95rem; }
        .admin-cms-nav a { color: #c9a227; font-weight: 600; }
        .admin-orders-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .admin-orders-table th, .admin-orders-table td { border: 1px solid #444; padding: 0.5rem 0.4rem; text-align: left; }
        .admin-orders-table th { background: #222; }
        .admin-orders-table tr:nth-child(even) { background: #1a1a1a; }
    </style>
</head>
<body>
<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-cms-wrap">
    <?php require __DIR__ . '/partials/admin_nav.php'; ?>

    <h1>View orders</h1>
    <p>Read-only list of all orders. Use <a href="/admin/orders/export">Export orders</a> to download CSV or Excel.</p>

    <div style="overflow-x: auto;">
        <table class="admin-orders-table">
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
                            <a href="/admin/orders/tickets?order_id=<?= htmlspecialchars((string)($row['order_id'] ?? '0')) ?>">Codes</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
