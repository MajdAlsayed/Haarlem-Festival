<?php

namespace App\Controllers;

use App\Repositories\DanceSettingsRepository;
use App\Repositories\PhotosRepository;
use App\Repositories\SettingsRepository;
use App\Services\ArtistService;
use App\ViewModels\ArtistDetailViewModel;
use App\Exceptions\NotFoundException;

/** /dance/artist/{slug} */
class ArtistController
{
    private ArtistService $artistService;
    private PhotosRepository $photosRepository;
    private SettingsRepository $settingsRepository;
    private DanceSettingsRepository $danceSettings;

    /** Wire artist detail dependencies (artist data, photos, settings). */
    public function __construct()
    {
        $this->artistService = new ArtistService(
            new \App\Repositories\ArtistsRepository(),
            new \App\Repositories\EventRepository()
        );
        $this->photosRepository = new PhotosRepository();
        $this->settingsRepository = new SettingsRepository();
        $this->danceSettings = new DanceSettingsRepository();
    }

    /** Build /dance/artist/{slug} view data from DB + dance settings + artist_music config. */
    public function show(string $slug): void
    {
        try {
            $data = $this->artistService->getArtistDetail($slug);
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new NotFoundException('Artist not found');
        }

        $artist = $data['artist'];
        $galleryImages = $data['galleryImages'];
        $artistEvents = $data['artistEvents'];

        $app = $this->settingsRepository->getAll();
        $d = $this->danceSettings->getMergedWithConfig();
        $imgBase = $this->danceImageBaseUrl($d);

        // Long-form copy + track list: still in dance.php (no extra tables); could move to DB later.
        $fileConfig = require __DIR__ . '/../Config/dance.php';
        $cfg = isset($fileConfig['artist_music'][$slug]) && is_array($fileConfig['artist_music'][$slug])
            ? $fileConfig['artist_music'][$slug]
            : [];

        $cfgGallery = [];
        if (isset($cfg['gallery']) && is_array($cfg['gallery'])) {
            $cfgGallery = array_values(array_filter(
                $cfg['gallery'],
                static fn ($x) => is_string($x) && $x !== ''
            ));
        }

        $galleryTarget = $this->clampInt($d['artist_detail_gallery_target_count'] ?? null, 4, 1, 20);
        if (count($galleryImages) < $galleryTarget && count($cfgGallery) >= $galleryTarget) {
            $merged = array_values(array_unique(array_merge($galleryImages, $cfgGallery)));
            $galleryImages = count($merged) >= $galleryTarget
                ? array_slice($merged, 0, $galleryTarget)
                : array_slice($cfgGallery, 0, $galleryTarget);
        }

        $ctxHero = (string) $d['artist_detail_photos_context_hero'];
        $heroFile = $this->photosRepository->getFilename($ctxHero, $slug);
        if ($heroFile === null) {
            // Row image from artists table, else file from dance_settings.
            $heroFile = !empty($artist['image']) ? (string) $artist['image'] : (string) $d['artist_detail_hero_fallback'];
        }
        $heroImage = $imgBase . $heroFile;

        $ctxSched = (string) $d['artist_detail_photos_context_schedule'];
        $schedMap = $d['artist_detail_schedule_fallbacks'] ?? [];
        if (!is_array($schedMap)) {
            $schedMap = [];
        }
        $schedDefault = isset($schedMap['default']) && is_string($schedMap['default']) ? $schedMap['default'] : '';
        $scheduleFile = $this->photosRepository->getFilename($ctxSched, $slug)
            ?? (is_string($schedMap[$slug] ?? null) ? $schedMap[$slug] : $schedDefault);
        if ($scheduleFile === null || $scheduleFile === '') {
            // If DB/json dropped "default", keep old shipped asset path (same as dance.php).
            $scheduleFile = $schedDefault !== '' ? $schedDefault : 'Artist/hardwell6.png';
        }
        $scheduleImage = $imgBase . (string) $scheduleFile;

        $listPath = (string) ($d['event_detail_list_path'] ?? '/dance');
        $breadcrumbs = [
            ['label' => $d['breadcrumb_home_label'], 'url' => $app['home_path']],
            ['label' => $d['breadcrumb_dance_label'], 'url' => $listPath],
            ['label' => strtoupper((string) ($artist['name'] ?? '')), 'url' => null], // match event-detail venue crumb style
        ];

        $aboutParagraphs = isset($cfg['about']) && is_array($cfg['about']) ? $cfg['about'] : null;
        if ($aboutParagraphs === null && !empty($artist['bio'])) {
            $aboutParagraphs = [(string) $artist['bio']];
        }
        $careerHighlights = isset($cfg['highlights']) && is_array($cfg['highlights']) ? $cfg['highlights'] : null;
        $musicTracks = isset($cfg['tracks']) && is_array($cfg['tracks']) ? $cfg['tracks'] : [];
        $musicExtraTracks = isset($cfg['extra_tracks']) && is_array($cfg['extra_tracks']) ? $cfg['extra_tracks'] : [];
        $musicDisplayName = isset($cfg['display_name']) ? (string) $cfg['display_name'] : strtoupper((string) ($artist['name'] ?? ''));
        $musicRealName = isset($cfg['real_name']) ? (string) $cfg['real_name'] : (string) ($artist['name'] ?? '');
        $musicLocation = isset($cfg['location']) ? (string) $cfg['location'] : (string) $d['artist_detail_default_location'];
        $albumTitle = isset($cfg['album_title']) ? (string) $cfg['album_title'] : (string) $d['artist_detail_default_album_title'];
        $albumSub = isset($cfg['album_sub']) ? (string) $cfg['album_sub'] : (string) $d['artist_detail_default_album_sub'];

        $galleryStats = isset($cfg['gallery_stats']) && is_array($cfg['gallery_stats'])
            ? $cfg['gallery_stats']
            : $d['artist_detail_gallery_stats_fallback'];
        if (!is_array($galleryStats)) {
            // Mirrors dance_settings default when artist_music omits gallery_stats.
            $galleryStats = [['num' => '—', 'label' => 'PHOTOS'], ['num' => '—', 'label' => 'SHOWS']];
        }

        $careerImage = $galleryImages[0] ?? ($artist['image'] ?? '');

        $ctxMusic = (string) $d['artist_detail_photos_context_music'];
        $profileKey = $this->photoSlotKey($d['artist_detail_music_profile_slots'] ?? [], $slug, 'profile');
        $albumKey = $this->photoSlotKey($d['artist_detail_music_album_slots'] ?? [], $slug, 'album_cover');

        $profileFile = $this->photosRepository->getFilename($ctxMusic, $profileKey);
        $profileImage = $profileFile !== null
            ? $imgBase . $profileFile
            : $imgBase . (string) $d['artist_detail_music_profile_fallback'];

        $albumFile = $this->photosRepository->getFilename($ctxMusic, $albumKey);
        $albumCoverImage = $albumFile !== null
            ? $imgBase . $albumFile
            : $imgBase . (string) $d['artist_detail_music_album_fallback'];

        $maxTag = $this->clampInt($d['artist_detail_hero_tagline_max_chars'] ?? null, 160, 40, 500);
        $heroTagline = isset($cfg['hero_tagline']) ? trim((string) $cfg['hero_tagline']) : '';
        if ($heroTagline === '') {
            $bio = (string) ($artist['bio'] ?? '');
            if ($bio !== '') {
                // Reserve 3 chars for ellipsis when truncating.
                $cut = max(1, $maxTag - 3);
                $heroTagline = mb_strlen($bio) > $maxTag ? mb_substr($bio, 0, $cut) . '…' : $bio;
            }
        }
        $heroTagline = $heroTagline !== '' ? $heroTagline : null;

        $followUrl = isset($cfg['follow_url']) && is_string($cfg['follow_url']) && $cfg['follow_url'] !== ''
            ? $cfg['follow_url']
            : null;

        $viewModel = new ArtistDetailViewModel(
            $artist,
            $galleryImages,
            $artistEvents,
            $heroImage,
            $scheduleImage,
            $breadcrumbs,
            $app,
            $aboutParagraphs,
            $careerHighlights,
            $musicTracks,
            $musicExtraTracks,
            $musicDisplayName,
            $musicRealName,
            $musicLocation,
            $albumTitle,
            $albumSub,
            $galleryStats,
            $careerImage,
            $profileImage,
            $albumCoverImage,
            $heroTagline,
            $followUrl
        );

        require __DIR__ . '/../Views/Dance/ArtistDetail.php';
    }

    /** @param array<string, mixed> $d */
    private function danceImageBaseUrl(array $d): string
    {
        $base = (string) ($d['dance_images_base_path'] ?? '/images/dance/');
        if ($base === '') {
            return '/images/dance/';
        }
        return str_ends_with($base, '/') ? $base : $base . '/';
    }

    /** Resolve photo slot key by slug with a required "default" fallback slot. */
    private function photoSlotKey(array $slots, string $slug, string $ifMissing): string
    {
        $v = $slots[$slug] ?? $slots['default'] ?? $ifMissing;

        return is_string($v) && $v !== '' ? $v : $ifMissing;
    }

    /** Normalise ints from dance_settings (stored as strings in DB). */
    private function clampInt(mixed $raw, int $fallback, int $min, int $max): int
    {
        if (!is_numeric($raw)) {
            return max($min, min($max, $fallback));
        }
        $n = (int) $raw;

        return max($min, min($max, $n > 0 ? $n : $fallback));
    }
}

