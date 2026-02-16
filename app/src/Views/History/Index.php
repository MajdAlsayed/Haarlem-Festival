<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A Stroll Through History - Haarlem Festival</title>
</head>
<body>
<h1>A Stroll Through History</h1>

<h2>Historic Landmarks of Haarlem</h2>

<div class="locations">
    <?php foreach ($locations as $location): ?>
        <div class="location-card">
            <h3><?= htmlspecialchars($location->name) ?></h3>
            <p><?= htmlspecialchars($location->description) ?></p>

            <?php if ($location->pageId): ?>
                <a href="/history/location/<?= htmlspecialchars($location->slug) ?>">
                    READ MORE
                </a>
            <?php else: ?>
                <button disabled>READ MORE</button>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>