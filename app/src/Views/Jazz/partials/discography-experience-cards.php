<?php
/**
 * Gumbo-style discography cards (cover, audio play, title, meta). Included from gumbo-king.php when $discography is non-empty.
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
                    <article class="jazz-gumbo-experience-card jazz-gumbo-experience-card--disc">
                        <div class="jazz-gumbo-exp-media">
                            <div class="jazz-gumbo-exp-poster">
                                <img src="<?= $h($coverSrc) ?>" alt="" class="jazz-gumbo-exp-img" loading="lazy" decoding="async">
                            </div>
                            <?php if ($tAudio !== ''): ?>
                            <button type="button" class="jazz-gumbo-exp-play" aria-label="Play <?= $h($tTitle) ?>" title="Play">
                                <span class="jazz-gumbo-exp-play-icon" aria-hidden="true">▶</span>
                            </button>
                            <audio class="jazz-gumbo-exp-audio" src="<?= $h($tAudio) ?>" preload="metadata"></audio>
                            <?php endif; ?>
                        </div>
                        <div class="jazz-gumbo-exp-body">
                            <div class="jazz-gumbo-exp-head">
                                <span class="jazz-gumbo-exp-artist"><?= $h($tTitle) ?></span>
                                <span class="jazz-gumbo-exp-meta"><?= $h($metaLine) ?></span>
                            </div>
                            <p class="jazz-gumbo-exp-desc"><?= $h($viewModel->artistTitle) ?> — <?= $h($descLine) ?></p>
                        </div>
                    </article>
<?php endforeach; ?>
