<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
$app = (new \App\Repositories\SettingsRepository())->getAll();

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($vm->pageTitle ?? 'Haarlem Stories - Events to Explore') ?></title>

    <link rel="stylesheet" href="/css/style.css?v=<?= h($app['css_version'] ?? '1') ?>">

    <style>
        body.stories-dark {
            color: #f0f0f0;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .stories-hero-banner{
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            height: 480px;
            overflow: hidden;
        }

        .hero-img{
            background-size: cover;
            background-position: center;
        }

        .hero-overlay{
            position: absolute;
            bottom: 60px;
            left: 60px;
            color: white;
            z-index: 2;
        }

        .hero-overlay h1{
            font-size: 3rem;
            font-weight: 800;
            margin: 0;
        }

        .hero-overlay p{
            margin-top: 10px;
            font-size: 1rem;
            opacity: 0.9;
        }

        .stories-heading-section{
            background: #d6c39a;
            color: #141414;
            padding: 2.2rem 1rem 2rem;
            border-top: 3px solid #ffcc00;
        }

        .stories-heading-inner{
            max-width: 1100px;
            margin: 0 auto;
        }

        .stories-heading-title{
            font-size: 1.35rem;
            letter-spacing: 0.08em;
            font-weight: 800;
            margin: 0 0 1rem;
            text-transform: uppercase;
        }

        .stories-heading-text{
            max-width: 980px;
            line-height: 1.7;
            margin: 0;
            font-size: 0.98rem;
            color: #1a1a1a;
        }

        .stories-scroll-down{
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-top: 1.2rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1a1a1a;
            opacity: 0.9;
        }

        .stories-scroll-icon{
            width: 30px;
            height: 30px;
            border: 2px solid rgba(0,0,0,0.35);
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .stories-venues{
            background: #0a0a1f;
            padding: 2rem 1rem;
            text-align: center;
        }

        .venues-container{
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .venue-btn{
            background: #ff9900;
            color: #000;
            padding: 0.6rem 1.5rem;
            border-radius: 999px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .venue-btn:hover{
            background: #ffbb44;
            transform: translateY(-3px);
        }

        .stories-hero {
            text-align: left;
            padding: 3rem 1rem 1.5rem;
            position: relative;
            max-width: 1100px;
            margin: 0 auto;
        }

        .stories-hero h1 {
            color: #ffcc00;
            font-size: 2.6rem;
            margin: 0 0 0.6rem;
        }

        .stories-subtitle {
            color: #ccc;
            font-size: 1.05rem;
            max-width: 720px;
            margin: 0;
        }

        .info-bubble {
            position: absolute;
            top: 2.5rem;
            right: 1rem;
            background: #fff;
            color: #000;
            padding: 1rem 1.4rem;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.6);
            font-size: 0.95rem;
            max-width: 300px;
            line-height: 1.4;
        }

        .info-bubble strong {
            color: #e00;
        }

        .day-tabs {
            display: flex;
            justify-content: center;
            gap: 0.8rem;
            flex-wrap: wrap;
            margin: 2rem 0 3rem;
        }

        .day-tab {
            background: #222244;
            color: #ddd;
            padding: 0.75rem 1.5rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }

        .day-tab.active,
        .day-tab:hover {
            background: #ff9900;
            color: #000;
        }

        .schedule-section {
            padding: 0 1rem 4rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .schedule-row{
            display: grid;
            grid-template-columns: 170px 1fr;
            gap: 1.2rem;
            align-items: center;
            margin: 1.2rem 0;
        }

        .schedule-label{
            color: #ddd;
            font-size: 0.95rem;
            text-align: left;
            opacity: 0.9;
            white-space: nowrap;
        }

        .schedule-content{
            width: 100%;
        }

        .day-tabs-inline{
            justify-content: flex-start;
            margin: 0;
        }

        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem 1.2rem;
            align-items: center;
            min-height: 80px;
            justify-content: flex-start;
        }

        .event-tag {
            background: #ffcc00;
            color: #000;
            padding: 0.75rem 1.4rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 180px;
            text-align: center;
            line-height: 1.4;
        }

        .stories-cards {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 1rem 4rem;
        }

        .stories-cards-grid{
            display:grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            align-items: stretch;
        }

        .stories-card{
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.03);
            box-shadow: 0 10px 28px rgba(0,0,0,0.55);
            display: flex;
            flex-direction: column;
            min-height: 420px;
        }

        .stories-card-img{
            height: 210px;
            background-size: cover;
            background-position: center;
            flex: 0 0 auto;
            filter: saturate(0.95);
        }

        .stories-card-body{
            padding: 14px 14px 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1;
        }

        .stories-card-title{
            margin: 0;
            font-size: 15px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.25;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.5em;
        }

        .stories-card-meta{
            font-size: 12px;
            color: rgba(255,255,255,0.82);
            display: grid;
            gap: 6px;
        }

        .meta-row{
            display:flex;
            align-items:center;
            gap: 8px;
        }

        .meta-ico{
            width: 18px;
            height: 18px;
            border-radius: 4px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            background: rgba(255,204,0,0.14);
        }

        .stories-card-desc{
            margin: 0;
            font-size: 12.5px;
            line-height: 1.55;
            color: rgba(255,255,255,0.78);
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3.7em;
        }

        .stories-card-actions{
            display:flex;
            gap: 10px;
            margin-top: 8px;
        }

        .card-btn{
            flex: 1;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            text-decoration:none;
            font-weight: 900;
            font-size: 12px;
            letter-spacing: .06em;
            text-transform: uppercase;
            border-radius: 8px;
            padding: 12px 10px;
            transition: .2s;
            border: 1px solid rgba(255,153,0,0.65);
        }

        .card-btn.primary{
            background: #ffb400;
            border-color: #ffb400;
            color: #000;
        }

        .card-btn.primary:hover{
            background: #ffcc55;
            border-color: #ffcc55;
            transform: translateY(-1px);
        }

        .card-btn.outline{
            background: rgba(0,0,0,0.25);
            color: #ffb400;
        }

        .card-btn.outline:hover{
            background: #ff9900;
            color: #000;
            border-color: #ff9900;
            transform: translateY(-1px);
        }

        @media (max-width: 1100px){
            .stories-cards-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 650px){
            .stories-cards-grid{ grid-template-columns: 1fr; }
        }

        @media (max-width: 900px) {
            .event-tag { min-width: 160px; font-size: 0.9rem; padding: 0.65rem 1.2rem; }
            .info-bubble { position: static; margin-top: 1rem; max-width: 100%; }
            .schedule-row{ grid-template-columns: 1fr; gap: 0.6rem; align-items: start; }
            .schedule-label{ white-space: normal; }
        }

        .stories-map-section{
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 1rem 4rem;
            text-align: center;
        }

        .stories-map-title{
            color: #ffcc00;
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin: 0 0 0.3rem;
        }

        .stories-map-subtitle{
            color: #cfcfcf;
            margin: 0 0 1.2rem;
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .map-venue-buttons{
            display:flex;
            flex-wrap:wrap;
            justify-content:center;
            gap: 0.8rem;
            margin: 0.8rem 0 1.2rem;
        }

        .map-venue-btn{
            background: #e7e7e7;
            color:#111;
            border: none;
            padding: 0.55rem 1.1rem;
            border-radius: 999px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(0,0,0,0.35);
            transition: 0.2s;
        }

        .map-venue-btn:hover{
            transform: translateY(-2px);
        }

        .map-venue-btn.active{
            background: #ff9900;
        }

        .stories-map-frame{
            width: 100%;
            max-width: 1000px;
            margin: 0 auto 1.5rem;
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid rgba(255, 204, 0, 0.45);
            box-shadow: 0 10px 28px rgba(0,0,0,0.55);
            background: rgba(0,0,0,0.2);
        }

        .stories-map-frame iframe{
            display: block;
            width: 100%;
            height: 520px;
            border: 0;
        }

        .btn-map{
            display: inline-block;
            background: #ff9900;
            color: #000;
            padding: 0.85rem 1.4rem;
            border-radius: 10px;
            font-weight: 800;
            text-decoration: none;
            letter-spacing: 0.02em;
            transition: 0.2s;
        }

        .btn-map:hover{
            background: #ffbb44;
            transform: translateY(-2px);
        }

        @media (max-width: 700px){
            .stories-map-frame iframe{ height: 380px; }
        }
    </style>
</head>

<body class="stories-dark">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

    <section class="stories-hero-banner">
        <?php foreach ($storiesHeroImages as $img): ?>
            <div class="hero-img" style="background-image: url('<?= h($img) ?>');"></div>
        <?php endforeach; ?>

        <div class="hero-overlay">
            <h1>Welcome to Stories<br>In Haarlem</h1>
            <p>Experience Haarlem Through Stories – Past, Present & Future.</p>
        </div>
    </section>

    <section class="stories-heading-section">
        <div class="stories-heading-inner">
            <h2 class="stories-heading-title">THE CITY THAT SPEAKS THROUGH ITS PEOPLE</h2>
            <p class="stories-heading-text">
                Haarlem’s rich tradition of storytelling lives in every corner of the city — from narrow cobblestone streets
                to centuries-old courtyards. During Stories in Haarlem, local residents, historians, and performers bring
                hidden tales to life through intimate sessions that reveal the city’s humor, heart, and heritage.
                These stories capture Haarlem’s spirit across generations: wartime memories whispered in quiet cafés,
                family legends passed down in living rooms, and personal journeys shaped by the city’s evolving culture.
                Together, they offer a rare glimpse into the lives behind Haarlem’s facades — the voices that make this city feel alive.
            </p>

            <div class="stories-scroll-down">
                <span class="stories-scroll-icon">⌄</span>
                <span>Scroll down</span>
            </div>
        </div>
    </section>

    <section class="stories-venues">
        <div class="venues-container">
            <a href="#" class="venue-btn">Verhalenhuis Haarlem</a>
            <a href="/stories/venue?slug=de-schuur" class="venue-btn">De Schuur</a>
            <a href="/stories/venue?slug=kweekcafe" class="venue-btn">Kweekcafé</a>
            <a href="#" class="venue-btn">Ten Boom Museum</a>
            <a href="#" class="venue-btn">Elswout Theater</a>
        </div>
    </section>

    <div class="stories-hero">
        <h1>Haarlem Stories - Events to Explore</h1>
        <p class="stories-subtitle">
            Discover the best of Haarlem through various storytelling experiences.<br>
            Check age labels for each event.
        </p>
        <div class="info-bubble">
            <strong>!</strong> 😊 Stories are available for different age groups.<br>
            Please check the age label.
        </div>
    </div>

        <section class="schedule-section">

        <div class="schedule-row">
            <div class="schedule-label">Select the Day:</div>
            <div class="schedule-content">
                <div class="day-tabs day-tabs-inline">
                    <?php
                    $days = [
                        'all' => 'All Event ›',
                        'thursday' => 'Thursday ›',
                        'friday' => 'Friday ›',
                        'saturday' => 'Saturday ›',
                        'sunday' => 'Sunday ›'
                    ];
                    foreach ($days as $key => $label): ?>
                        <a class="day-tab <?= $vm->isActive($key) ? 'active' : '' ?>"
                           href="/stories?day=<?= h($key) ?>"><?= h($label) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php $selected = $vm->selectedDay ?? 'all'; ?>

        <?php if ($selected !== 'all'): ?>
            <?php
            $nlBlocks  = $vm->schedule['NL'][$selected] ?? [];
            $engBlocks = $vm->schedule['ENG'][$selected] ?? [];
            ?>

            <div class="schedule-row">
                <div class="schedule-label">Dutch Events:</div>
                <div class="schedule-content">
                    <div class="tags-container">
                        <?php if (empty($nlBlocks)): ?>
                            <span style="color:#cfcfcf;">No Dutch events.</span>
                        <?php else: ?>
                            <?php foreach ($nlBlocks as $b): ?>
                                <span class="event-tag">
                                    <?= h($b['time'] ?? '') ?><br>
                                    <?php if (!empty($b['age'])): ?>
                                        Age <?= h($b['age']) ?>
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="schedule-row">
                <div class="schedule-label">English Events:</div>
                <div class="schedule-content">
                    <div class="tags-container">
                        <?php if (empty($engBlocks)): ?>
                            <span style="color:#cfcfcf;">No English events.</span>
                        <?php else: ?>
                            <?php foreach ($engBlocks as $b): ?>
                                <span class="event-tag">
                                    <?= h($b['time'] ?? '') ?><br>
                                    <?php if (!empty($b['age'])): ?>
                                        Age <?= h($b['age']) ?>
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </section>

    <section class="stories-cards">
        <div class="stories-cards-grid">

            <?php if (empty($vm->stories)): ?>
                <p style="color:#cfcfcf; max-width:1100px; margin: 0 auto; padding: 20px 10px;">
                    No stories found. Run your StoriesSeeder and make sure stories table has rows.
                </p>
            <?php endif; ?>

            <?php foreach ($vm->stories as $s): ?>
                <?php
                $lang = $s['language'] ?? '';
                $age  = $s['age'] ?? '';
                $time = $s['start_time'] ?? '';
                $img  = $s['image_path'] ?? '';
                if (!$img) $img = '/images/Stories/cards/default.jpg';
                ?>
                <article class="stories-card">
                    <div class="stories-card-img" style="background-image:url('<?= h($img) ?>')"></div>

                    <div class="stories-card-body">
                        <h3 class="stories-card-title"><?= h($s['story_name'] ?? $s['name'] ?? 'Story') ?></h3>

                        <div class="stories-card-meta">
                            <div class="meta-row">
                                <span class="meta-ico">📅</span>
                                <span><?= h(ucfirst($s['event_day'] ?? '')) ?> <?= h($time) ?></span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-ico">📍</span>
                                <span><?= h($s['venue_name'] ?? '') ?><?= !empty($s['venue_city']) ? ', ' . h($s['venue_city']) : '' ?></span>
                            </div>

                            <?php if ($lang): ?>
                                <div class="meta-row">
                                    <span class="meta-ico">🌐</span>
                                    <span>Lang: <?= h($lang) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($age): ?>
                                <div class="meta-row">
                                    <span class="meta-ico">🔞</span>
                                    <span>Age <?= h($age) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <p class="stories-card-desc"><?= h($s['description'] ?? '') ?></p>

                        <div class="stories-card-actions">
                            <a class="card-btn primary" href="/tickets">BUY TICKETS</a>
                            <a class="card-btn outline" href="/stories/detail?id=<?= (int)($s['story_id'] ?? 0) ?>">MORE INFO</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="stories-map-section">
        <h2 class="stories-map-title">Places To Visit For Events</h2>
        <p class="stories-map-subtitle">Location: Haarlem, Netherlands</p>

        <div class="map-venue-buttons">
            <button type="button" class="map-venue-btn" data-lat="52.40385" data-lng="4.64628">Verhalenhuis Haarlem</button>
            <button type="button" class="map-venue-btn" data-lat="52.3818" data-lng="4.63931">Schuur</button>
            <button type="button" class="map-venue-btn" data-lat="52.39613" data-lng="4.63569">Kweekcafé</button>
            <button type="button" class="map-venue-btn" data-lat="52.38227" data-lng="4.6354">Ten Boom Museum</button>
            <button type="button" class="map-venue-btn" data-lat="52.37631" data-lng="4.59906">Elswout Theater</button>
        </div>

        <div class="stories-map-frame">
            <iframe
                id="storiesMap"
                src="https://www.google.com/maps/d/u/0/embed?mid=1Y0K04QlhJ-dwhjVOe-8iT3bFJQ5yPAw&ll=52.387877486255384%2C4.630817341343083&z=14"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen>
            </iframe>
        </div>

        <a class="btn-map"
           href="https://www.google.com/maps/d/u/0/viewer?mid=1Y0K04QlhJ-dwhjVOe-8iT3bFJQ5yPAw&ll=52.387877486255384%2C4.630817341343083&z=14"
           target="_blank" rel="noopener">
            View Live Map &#8250;
        </a>
    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
(function () {
    const map = document.getElementById('storiesMap');
    if (!map) return;

    const mid = '1Y0K04QlhJ-dwhjVOe-8iT3bFJQ5yPAw';
    const z = 16;

    document.querySelectorAll('.map-venue-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const lat = btn.dataset.lat;
            const lng = btn.dataset.lng;
            if (!lat || !lng) return;

            map.src = `https://www.google.com/maps/d/u/0/embed?mid=${mid}&ll=${encodeURIComponent(lat + ',' + lng)}&z=${z}`;
            map.scrollIntoView({ behavior: 'smooth', block: 'center' });

            document.querySelectorAll('.map-venue-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
})();
</script>

</body>
</html>