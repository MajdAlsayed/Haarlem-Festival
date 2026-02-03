<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page->title) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=1">
</head>
<body>

<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="hero">
    <h1><?= htmlspecialchars($page->title) ?></h1>
    <p><?= nl2br(htmlspecialchars($page->content)) ?></p>
    <a class="btn" href="#">Explore Now</a>
</section>

<section>
    <h2>Upcoming Festival & Events</h2>
    <div class="cards">
        <div class="card">Music & Culture</div>
        <div class="card">Historic Haarlem</div>
        <div class="card">Jazz</div>
        <div class="card">Food</div>
        <div class="card">Stories</div>
    </div>
</section>

<footer>
    © Haarlem Festival
</footer>

</body> 
</html>
