<?php
/**
 * Shared Jazz discography media cards.
 *
 * @var \App\ViewModels\JazzArtistViewModel $viewModel
 * @var list<array<string,mixed>> $discography
 * @var callable(string):string $h
 * @var list<string>|null $discCoverAlternates Optional [urlA, urlB] when track has no cover image
 */
if ($discography === []) {
    return;
}
$covers = (isset($discCoverAlternates) && is_array($discCoverAlternates) && $discCoverAlternates !== [])
    ? array_values($discCoverAlternates)
    : [$viewModel->heroImage, $viewModel->heroImage];
$nCovers = count($covers);
foreach ($discography as $idx => $track):
    $tTitle = (string) ($track['title'] ?? 'Track');
    $tAudio = (string) ($track['audio_url'] ?? '');
    $tImg = (string) ($track['image_url'] ?? '');
    $fallback = $covers[$idx % $nCovers];
    $coverSrc = $tImg !== '' ? $tImg : $fallback;
    $ry = $track['release_year'] ?? null;
    $metaLine = $ry !== null && $ry !== ''
        ? 'Release ' . (string) (int) $ry
        : 'Discography';
    $pc = (int) ($track['play_count'] ?? 0);
    $descLine = $pc > 0
        ? 'Track played ' . number_format($pc) . ' times'
        : 'Listen to the full track.';
    ?>
                    <article class="jazz-media-card jazz-discography-card">
                        <div class="jazz-media-card__media">
                            <div class="jazz-media-card__poster">
                                <img src="<?= $h($coverSrc) ?>" alt="" class="jazz-media-card__img" loading="lazy" decoding="async">
                            </div>
                            <?php if ($tAudio !== ''): ?>
                            <button type="button" class="jazz-media-card__play" aria-label="Play <?= $h($tTitle) ?>" title="Play">
                                <span class="jazz-media-card__play-icon" aria-hidden="true">▶</span>
                            </button>
                            <audio class="jazz-media-card__audio" src="<?= $h($tAudio) ?>" preload="metadata"></audio>
                            <?php endif; ?>
                        </div>
                        <div class="jazz-media-card__body">
                            <div class="jazz-media-card__head">
                                <span class="jazz-media-card__title"><?= $h($tTitle) ?></span>
                                <span class="jazz-media-card__meta"><?= $h($metaLine) ?></span>
                            </div>
                            <p class="copy-text copy-text--sm jazz-media-card__desc"><?= $h($viewModel->artistTitle) ?> — <?= $h($descLine) ?></p>
                        </div>
                    </article>
<?php endforeach; ?>
