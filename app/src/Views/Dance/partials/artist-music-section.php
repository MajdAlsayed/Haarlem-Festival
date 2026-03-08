<?php
/** Artist profile + featured release + album player + track cards. Uses $artist (from parent) for slug; Tiësto data set in ArtistDetail. */
$musicPhotos = new \App\Repositories\PhotosRepository();
$musicSlug = $artist['slug'] ?? 'hardwell';
$profileKey = $musicSlug === 'tiesto' ? 'profile_tiesto' : 'profile';
$albumKey = $musicSlug === 'tiesto' ? 'album_cover_tiesto' : 'album_cover';
$track1Key = $musicSlug === 'tiesto' ? 'track_1_tiesto' : 'track_1';
$track2Key = $musicSlug === 'tiesto' ? 'track_2_tiesto' : 'track_2';
$artistImg = '/images/dance/' . ($musicPhotos->getFilename('dance_artist_music', $profileKey) ?? ($musicSlug === 'tiesto' ? 'Artist/tiesto1.png' : 'Artist/hardwell1.png'));
$albumCover = '/images/dance/' . ($musicPhotos->getFilename('dance_artist_music', $albumKey) ?? ($musicSlug === 'tiesto' ? 'Artist/tiesto2.png' : 'Artist/hardwell2.jpg'));
if (!isset($musicTracks)) {
    $musicTracks = [
        ['title' => 'The Partycrasher', 'duration' => '2:54', 'audio' => '/audio/Hardwell & Chuckie - The Partycrasher (Hardwell & Friends Vol. 04).mp3'],
        ['title' => 'Lights Out', 'duration' => '4:54', 'audio' => '/audio/Hardwell & Olly James - Lights Out (Hardwell & Friends Vol. 04).mp3'],
        ['title' => 'Rise Again', 'duration' => '2:78', 'audio' => '/audio/Hardwell & Ryos - Rise Again (Hardwell & Friends Vol. 04).mp3'],
    ];
}
if (!isset($musicExtraTracks)) {
    $musicExtraTracks = [
        ['artist' => 'Hardwell, Dyro', 'title' => 'Not Alone', 'tag' => 'Dance', 'cover' => '/images/dance/' . ($musicPhotos->getFilename('dance_artist_music', $track1Key) ?? 'Artist/hardwell3.jpg'), 'audio' => '/audio/Hardwell & Dyro - Not Alone (Official Music Video).mp3'],
        ['artist' => 'Hardwell, Maddix', 'title' => 'Rave Till My Grave (feat. Villain)', 'tag' => 'Dance', 'cover' => '/images/dance/' . ($musicPhotos->getFilename('dance_artist_music', $track2Key) ?? 'Artist/hardwell4.jpg'), 'audio' => '/audio/Hardwell & Maddix feat. Villain - Rave Till My Grave.mp3'],
    ];
}
$tracks = $musicTracks;
$extraTracks = $musicExtraTracks;
$displayName = $musicDisplayName ?? 'HARDWELL';
$realName = $musicRealName ?? 'Robbert Hardwell';
$location = $musicLocation ?? 'Breda, Netherlands';
$albumTitle = $musicAlbumTitle ?? 'Hardwell & Friends Vol. 04';
$albumSub = $musicAlbumSub ?? 'VOL. 04';
?>
<section class="artist-music-section">
    <div class="artist-music-inner">
        <!-- Top: Artist Profile + Featured Release -->
        <div class="artist-music-top">
            <div class="artist-music-profile">
                <div class="artist-music-avatar">
                    <img src="<?= htmlspecialchars($artistImg) ?>" alt="<?= htmlspecialchars($realName) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <span class="artist-music-avatar-fallback"><?= htmlspecialchars(mb_substr($displayName, 0, 1)) ?></span>
                </div>
                <div class="artist-music-info">
                    <div class="artist-music-name-row">
                        <h2 class="artist-music-name"><?= htmlspecialchars($displayName) ?></h2>
                        <span class="artist-music-verified" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </span>
                    </div>
                    <p class="artist-music-real"><?= htmlspecialchars($realName) ?></p>
                    <p class="artist-music-location"><?= htmlspecialchars($location) ?></p>
                </div>
            </div>
            <div class="artist-music-featured">
                <div class="artist-music-featured-row">
                    <div class="artist-music-featured-cover">
                        <img src="<?= htmlspecialchars($albumCover) ?>" alt="<?= htmlspecialchars($albumTitle) ?>">
                    </div>
                    <div class="artist-music-featured-info">
                    <h3 class="artist-music-featured-title"><?= htmlspecialchars($albumTitle) ?></h3>
                    <p class="artist-music-featured-sub"><?= htmlspecialchars($albumSub) ?></p>
                    </div>
                </div>
                <button type="button" class="artist-music-featured-btn">OUT NOW</button>
                <p class="artist-music-featured-src">REVEALEDRECORDINGS.COM</p>
            </div>
        </div>

        <!-- Album Player -->
        <div class="artist-music-album">
            <div class="artist-music-album-header">
                <div class="artist-music-album-cover">
                    <img src="<?= htmlspecialchars($albumCover) ?>" alt="<?= htmlspecialchars($albumTitle) ?>">
                </div>
                <div class="artist-music-album-info">
                    <p class="artist-music-album-artist"><?= htmlspecialchars($displayName) ?></p>
                    <h3 class="artist-music-album-title"><?= htmlspecialchars($albumTitle) ?></h3>
                    <div class="artist-music-album-meta">EP • 2025</div>
                    <div class="artist-music-waveform-row" data-audio-group="album">
                        <div class="artist-music-vol-row">
                            <button type="button" class="artist-music-vol-btn js-vol-down" aria-label="Volume down">−</button>
                            <span class="artist-music-vol-label js-vol-value">100%</span>
                            <button type="button" class="artist-music-vol-btn js-vol-up" aria-label="Volume up">+</button>
                        </div>
                    </div>
                    <div class="artist-music-progress-row">
                        <span class="artist-music-time js-time-current">0:00</span>
                        <div class="artist-music-progress-wrap js-progress-wrap" data-audio-group="album">
                            <div class="artist-music-progress-bar js-progress-bar" style="width:0%"></div>
                        </div>
                        <span class="artist-music-time js-time-duration">0:00</span>
                    </div>
                    <span class="artist-music-tag">#Dance</span>
                </div>
            </div>
            <div class="artist-music-tracklist">
                <?php foreach ($tracks as $idx => $t): ?>
                <div class="artist-music-track-row" data-audio="<?= htmlspecialchars($t['audio'] ?? '') ?>">
                    <button type="button" class="artist-music-track-play js-music-play" aria-label="Play <?= htmlspecialchars($t['title']) ?>">
                        <svg class="icon-play" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        <svg class="icon-pause" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="display:none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                    </button>
                    <span class="artist-music-track-num"><?= $idx + 1 ?></span>
                    <div class="artist-music-track-thumb">
                        <img src="<?= htmlspecialchars($albumCover) ?>" alt="">
                    </div>
                    <span class="artist-music-track-title"><?= htmlspecialchars($t['title']) ?></span>
                    <span class="artist-music-track-dur"><?= htmlspecialchars($t['duration']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Extra Track Cards -->
        <div class="artist-music-tracks">
            <?php foreach ($extraTracks as $t): ?>
                <div class="artist-music-track-card" data-audio="<?= htmlspecialchars($t['audio'] ?? '') ?>">
                <div class="artist-music-track-card-cover">
                    <img src="<?= htmlspecialchars($t['cover'] ?? '') ?>" alt="<?= htmlspecialchars($t['title']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <span class="artist-music-track-card-fallback"><?= htmlspecialchars(mb_substr($t['artist'], 0, 1)) ?></span>
                </div>
                <div class="artist-music-track-card-body">
                    <div class="artist-music-track-card-top">
                        <button type="button" class="artist-music-track-card-play js-music-play" aria-label="Play <?= htmlspecialchars($t['title']) ?>">
                            <svg class="icon-play" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            <svg class="icon-pause" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" style="display:none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                        </button>
                        <div>
                            <p class="artist-music-track-card-artist"><?= htmlspecialchars($t['artist']) ?></p>
                            <h4 class="artist-music-track-card-title"><?= htmlspecialchars($t['title']) ?></h4>
                        </div>
                    </div>
                    <div class="artist-music-waveform-row" data-audio="<?= htmlspecialchars($t['audio'] ?? '') ?>">
                        <div class="artist-music-vol-row">
                            <button type="button" class="artist-music-vol-btn js-vol-down" aria-label="Volume down">−</button>
                            <span class="artist-music-vol-label js-vol-value">100%</span>
                            <button type="button" class="artist-music-vol-btn js-vol-up" aria-label="Volume up">+</button>
                        </div>
                    </div>
                    <div class="artist-music-progress-row">
                        <span class="artist-music-time js-time-current">0:00</span>
                        <div class="artist-music-progress-wrap js-progress-wrap" data-audio="<?= htmlspecialchars($t['audio'] ?? '') ?>">
                            <div class="artist-music-progress-bar js-progress-bar" style="width:0%"></div>
                        </div>
                        <span class="artist-music-time js-time-duration">0:00</span>
                    </div>
                    <span class="artist-music-tag">#<?= htmlspecialchars($t['tag']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <audio id="artist-music-audio" preload="metadata"></audio>
</section>
<script>
(function(){
    var audio = document.getElementById('artist-music-audio');
    if(!audio) return;
    var currentRow = null;
    var currentSrc = '';
    function fmt(t){ var m=Math.floor(t/60), s=Math.floor(t%60); return m+':'+(s<10?'0':'')+s; }
    function getActiveSection(){
        if(!currentRow) return null;
        var wrap = currentRow.closest('.artist-music-tracklist');
        if(wrap) return wrap.previousElementSibling;
        return currentRow.closest('.artist-music-track-card');
    }
    function updateActiveProgress(){
        var section = getActiveSection();
        if(!section) return;
        var bar = section.querySelector('.js-progress-bar');
        var tc = section.querySelector('.js-time-current');
        var td = section.querySelector('.js-time-duration');
        if(bar && audio.duration){ bar.style.width=(audio.currentTime/audio.duration)*100+'%'; }
        if(tc) tc.textContent=fmt(audio.currentTime);
        if(td) td.textContent=isNaN(audio.duration)?'0:00':fmt(audio.duration);
    }
    function resetAllProgress(){
        document.querySelectorAll('.js-progress-bar').forEach(function(b){ b.style.width='0%'; });
        document.querySelectorAll('.js-time-current').forEach(function(t){ t.textContent='0:00'; });
        document.querySelectorAll('.js-time-duration').forEach(function(t){ t.textContent='0:00'; });
    }
    function resetUi(){
        if(currentRow){ var b=currentRow.querySelector('.icon-play'); var p=currentRow.querySelector('.icon-pause'); if(b)b.style.display=''; if(p)p.style.display='none'; }
        currentRow=null;
        currentSrc='';
        resetAllProgress();
    }
    audio.addEventListener('timeupdate', updateActiveProgress);
    audio.addEventListener('loadedmetadata', updateActiveProgress);
    audio.addEventListener('durationchange', updateActiveProgress);
    audio.addEventListener('ended', resetUi);
    document.querySelectorAll('.js-progress-wrap').forEach(function(wrap){
        wrap.addEventListener('click', function(e){
            if(!audio.duration) return;
            var rect=this.getBoundingClientRect();
            var pct=Math.max(0,Math.min(1,(e.clientX-rect.left)/rect.width));
            audio.currentTime=pct*audio.duration;
            updateActiveProgress();
        });
    });
    document.querySelectorAll('.js-vol-down').forEach(function(btn){
        btn.addEventListener('click', function(){
            audio.volume=Math.max(0, audio.volume-0.1);
            document.querySelectorAll('.js-vol-value').forEach(function(v){ v.textContent=Math.round(audio.volume*100)+'%'; });
        });
    });
    document.querySelectorAll('.js-vol-up').forEach(function(btn){
        btn.addEventListener('click', function(){
            audio.volume=Math.min(1, audio.volume+0.1);
            document.querySelectorAll('.js-vol-value').forEach(function(v){ v.textContent=Math.round(audio.volume*100)+'%'; });
        });
    });
    function togglePlay(btn){
        var row = btn.closest('[data-audio]');
        var src = row && row.getAttribute('data-audio');
        if(!src) return;
        var playIcon = btn.querySelector('.icon-play');
        var pauseIcon = btn.querySelector('.icon-pause');
        var isSameTrack = (currentSrc === src);
        if(isSameTrack && !audio.paused){
            audio.pause();
            if(playIcon) playIcon.style.display = '';
            if(pauseIcon) pauseIcon.style.display = 'none';
            currentRow = null;
            currentSrc = '';
            resetAllProgress();
        } else {
            if(currentRow && currentRow !== row){ var b=currentRow.querySelector('.icon-play'); var p=currentRow.querySelector('.icon-pause'); if(b)b.style.display=''; if(p)p.style.display='none'; }
            currentSrc = src;
            audio.src = src;
            audio.play().catch(function(){});
            if(playIcon) playIcon.style.display = 'none';
            if(pauseIcon) pauseIcon.style.display = '';
            currentRow = row;
            updateActiveProgress();
        }
    }
    document.querySelectorAll('.js-music-play').forEach(function(btn){
        btn.addEventListener('click', function(){ togglePlay(this); });
    });
})();
</script>
