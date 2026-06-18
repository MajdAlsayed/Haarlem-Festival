<?php
/**
 * Shared band-member grid (Gumbo olive/orange layout). Used by gumbo-king.php when $members is non-empty.
 * Shared “Gumbo style” band block: olive panel, orange row bars, overlapping photos.
 *
 * @var callable(string):string $h
 * @var list<array{name:string,role:string,img:string}> $members
 */
if ($members === []) {
    return;
}
$row1 = array_slice($members, 0, 3);
$row2 = array_slice($members, 3);
?>
        <section class="festival-card jazz-band-section">
            <h3 class="section-title section-title--accent section-title--underlined jazz-band-title">Band Members</h3>
            <div class="jazz-band-rows">
                <div class="jazz-band-row jazz-band-row--three" aria-label="Band members row 1">
                    <?php foreach ($row1 as $m): ?>
                    <article class="jazz-band-member-card">
                        <div class="jazz-band-member-photo">
                            <img src="<?= $h($m['img']) ?>" alt="<?= $h($m['name']) ?>">
                        </div>
                        <div class="jazz-band-member-captions">
                            <strong class="section-lead jazz-band-member-name"><?= $h($m['name']) ?></strong>
                            <span class="copy-text copy-text--sm jazz-band-member-role"><?= $h($m['role']) ?></span>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php if ($row2 !== []):
                    $row2Class = 'jazz-band-row jazz-band-row--two';
                    if (count($row2) >= 3) {
                        $row2Class .= 'jazz-band-row--three';
                    }
                ?>
                <div class="<?= $h($row2Class) ?>" aria-label="Band members row 2">
                    <?php foreach ($row2 as $m): ?>
                    <article class="jazz-band-member-card">
                        <div class="jazz-band-member-photo">
                            <img src="<?= $h($m['img']) ?>" alt="<?= $h($m['name']) ?>">
                        </div>
                        <div class="jazz-band-member-captions">
                            <strong class="section-lead jazz-band-member-name"><?= $h($m['name']) ?></strong>
                            <span class="copy-text copy-text--sm jazz-band-member-role"><?= $h($m['role']) ?></span>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
