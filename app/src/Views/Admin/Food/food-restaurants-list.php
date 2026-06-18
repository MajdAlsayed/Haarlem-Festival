<?php
/**
 * /admin/food/restaurants  — Restaurant list with delete action
 *
 * @var \App\Models\Restaurant[]       $restaurants All restaurant rows
 * @var string|null                    $success     Flash success message
 * @var string|null                    $error       Flash error message
 */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$renderStars = static function (int $n): string {
    $n = max(0, min(5, $n));
    return str_repeat('★', $n) . str_repeat('☆', 5 - $n);
};

$pageTitle = 'Restaurants — Admin — ' . ('Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../../partials/head.php'; ?>
<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--wide">

        <nav class="admin-breadcrumb">
            <a href="/"><?= $h('Haarlem Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/food">Food</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Restaurants</span>
        </nav>

        <h1 class="admin-title">Restaurants</h1>
        <p class="admin-lead">
            Create, edit, or remove restaurants.
            <a href="/food" target="_blank" rel="noopener">View food page ↗</a>
        </p>

        <?php if (!empty($success)): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <p>
            <a href="/admin/food/restaurants/new" class="admin-btn admin-btn-primary">Add restaurant</a>
            <a href="/admin/food" class="admin-btn admin-btn-secondary">Back to Food CMS</a>
        </p>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Stars</th>
                        <th>Seats</th>
                        <th>Price adult / kid</th>
                        <th>Sessions</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($restaurants)): ?>
                        <tr>
                            <td colspan="8" class="admin-muted" style="text-align:center;padding:1.5rem 0;">
                                No restaurants yet. <a href="/admin/food/restaurants/new">Add the first one.</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($restaurants as $r): ?>
                            <tr>
                                <td><?= (int) $r->restaurantId ?></td>
                                <td>
                                    <strong><?= $h($r->name) ?></strong><br>
                                    <small class="admin-muted"><?= $h($r->address) ?></small>
                                </td>
                                <td><?= $h($r->type) ?></td>
                                <td class="food-stars" title="<?= (int) $r->stars ?> stars">
                                    <?= $h($renderStars((int) $r->stars)) ?>
                                </td>
                                <td><?= (int) $r->seats ?></td>
                                <td>
                                    €<?= $h(number_format($r->priceAdult, 2)) ?> /
                                    €<?= $h(number_format($r->priceKid, 2)) ?>
                                </td>
                                <td><?= (int) $r->sessions ?></td>
                                <td>
                                    <a href="/admin/food/restaurants/edit?id=<?= (int) $r->restaurantId ?>"
                                       class="admin-btn admin-btn-sm admin-btn-primary">Edit</a>
                                    <?php
                                    $delForm = 'admin_food_del_' . (int) $r->restaurantId;
                                    $delTok  = \App\Core\Csrf::token($delForm);
                                    ?>
                                    <form method="post" action="/admin/food/restaurants/delete"
                                          style="display:inline;"
                                          onsubmit="return confirm('Delete <?= $h(addslashes($r->name)) ?>? This cannot be undone.');">
                                        <input type="hidden" name="_csrf_form" value="<?= $h($delForm) ?>">
                                        <input type="hidden" name="_csrf"      value="<?= $h($delTok) ?>">
                                        <input type="hidden" name="restaurant_id" value="<?= (int) $r->restaurantId ?>">
                                        <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>