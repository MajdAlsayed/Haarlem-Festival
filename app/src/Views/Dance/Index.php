<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dance Festival</title>
    <link rel="stylesheet" href="/css/style.css?v=1">
</head>
<body>

<h1>Dance Festival</h1>

<?php foreach ($events as $event): ?>
    <div style="margin-bottom:20px;">
        <h3><?= htmlspecialchars($event->title) ?></h3>
        <p><?= htmlspecialchars($event->description) ?></p>
        <small><?= htmlspecialchars($event->location) ?></small>
    </div>
<?php endforeach; ?>

</body>
</html>
