<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\DanceArtistServiceInterface;
use App\Exceptions\NotFoundException;
use App\Models\Event;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\PhotosRepository;
use App\Repositories\SettingsRepository;
use App\ViewModels\ArtistDetailViewModel;

// builds the whole /dance/artist/{slug} page into one view model
class DanceArtistService implements DanceArtistServiceInterface
{
    public function __construct(
        private ArtistService $artistService,
        private EventService $eventService,
        private PhotosRepository $photosRepository,
        private SettingsRepository $settingsRepository,
        private DanceSettingsRepository $danceSettingsRepository
    ) {
    }

    public function buildDetailViewModel(string $slug): ArtistDetailViewModel
    {
        $artist = $this->artistService->getBySlug($slug);
        if (!$artist) {
            throw new NotFoundException('Artist not found');
        }

        $artistName = '';
        if (isset($artist['name'])) {
            $artistName = (string) $artist['name'];
        }

        $app = $this->settingsRepository->getAll();
        $dance = $this->danceSettingsRepository->getMergedWithConfig();
        $config = $this->artistMusicConfig($slug);
        $imageBase = $this->danceImageBaseUrl($dance);

        // work everything out first, then hand it to the view model
        $galleryImages = $this->resolveGallery($this->artistService->getPhotoFilenames($artist['id']), $config, $dance);
        $artistEvents = $this->danceEventsForArtist($artist);
        $heroImage = $imageBase . $this->resolveHeroFile($artist, $slug, $dance);
        $scheduleImage = $imageBase . $this->resolveScheduleFile($slug, $dance);
        $breadcrumbs = $this->buildBreadcrumbs($artist, $dance, $app);
        [$profileImage, $albumCoverImage] = $this->resolveMusicImages($slug, $dance, $imageBase);
        $galleryStats = $this->resolveGalleryStats($config, $dance);
        $heroTagline = $this->resolveHeroTagline($config, $artist, $dance);
        $followUrl = $this->resolveFollowUrl($config);

        // the long-form copy (about text, highlights, tracks) still lives in dance.php
        $aboutParagraphs = $this->configArrayOrNull($config, 'about');
        if ($aboutParagraphs === null && !empty($artist['bio'])) {
            $aboutParagraphs = [(string) $artist['bio']];
        }
        $careerHighlights = $this->configArrayOrNull($config, 'highlights');
        $musicTracks = $this->configArrayOrEmpty($config, 'tracks');
        $musicExtraTracks = $this->configArrayOrEmpty($config, 'extra_tracks');

        // career image: the first gallery photo, or the artist's own image
        $careerImage = '';
        if (isset($galleryImages[0])) {
            $careerImage = $galleryImages[0];
        } elseif (isset($artist['image'])) {
            $careerImage = (string) $artist['image'];
        }

        // the music text bits: use the config value, otherwise the settings/default
        $musicDisplayName = $this->configText($config, 'display_name', strtoupper($artistName));
        $musicRealName = $this->configText($config, 'real_name', $artistName);
        $musicLocation = $this->configText($config, 'location', (string) $dance['artist_detail_default_location']);
        $albumTitle = $this->configText($config, 'album_title', (string) $dance['artist_detail_default_album_title']);
        $albumSub = $this->configText($config, 'album_sub', (string) $dance['artist_detail_default_album_sub']);

        return new ArtistDetailViewModel(
            artist: $artist,
            galleryImages: $galleryImages,
            artistEvents: $artistEvents,
            heroImage: $heroImage,
            scheduleImage: $scheduleImage,
            breadcrumbs: $breadcrumbs,
            appSettings: $app,
            aboutParagraphs: $aboutParagraphs,
            careerHighlights: $careerHighlights,
            musicTracks: $musicTracks,
            musicExtraTracks: $musicExtraTracks,
            musicDisplayName: $musicDisplayName,
            musicRealName: $musicRealName,
            musicLocation: $musicLocation,
            albumTitle: $albumTitle,
            albumSub: $albumSub,
            galleryStats: $galleryStats,
            careerImage: $careerImage,
            profileImage: $profileImage,
            albumCoverImage: $albumCoverImage,
            heroTagline: $heroTagline,
            followUrl: $followUrl
        );
    }

    // the dance events whose title mentions this artist
    /** @return Event[] */
    private function danceEventsForArtist(array $artist): array
    {
        // event titles spell it "Hardwell", so match on that name for that slug
        $searchName = $artist['slug'] === 'hardwell' ? 'Hardwell' : $artist['name'];

        $events = [];
        foreach ($this->eventService->getByCategory('dance') as $event) {
            // keep the event if it has a title that mentions the artist
            if ($event->title !== null && stripos($event->title, $searchName) !== false) {
                $events[] = $event;
            }
        }

        return $events;
    }

    // the artist_music block for this slug from dance.php, or empty if there isn't one
    private function artistMusicConfig(string $slug): array
    {
        $config = require __DIR__ . '/../Config/dance.php';
        if (isset($config['artist_music'][$slug]) && is_array($config['artist_music'][$slug])) {
            return $config['artist_music'][$slug];
        }

        return [];
    }

    // the dance images base path, always ending with a slash
    private function danceImageBaseUrl(array $dance): string
    {
        $base = (string) ($dance['dance_images_base_path'] ?? '/images/dance/');
        if ($base === '') {
            return '/images/dance/';
        }

        return str_ends_with($base, '/') ? $base : $base . '/';
    }

    // gallery photos from the db, topped up from dance.php when there aren't enough
    /** @return string[] */
    private function resolveGallery(array $galleryImages, array $config, array $dance): array
    {
        $configGallery = $this->configGalleryImages($config);
        $target = $this->clampInt($dance['artist_detail_gallery_target_count'] ?? null, 4, 1, 20);

        // only top up from config when the db is short and config can actually cover the target
        if (count($galleryImages) < $target && count($configGallery) >= $target) {
            $merged = array_values(array_unique(array_merge($galleryImages, $configGallery)));

            return count($merged) >= $target
                ? array_slice($merged, 0, $target)
                : array_slice($configGallery, 0, $target);
        }

        return $galleryImages;
    }

    // the gallery image names listed in dance.php (skipping any empty ones)
    /** @return string[] */
    private function configGalleryImages(array $config): array
    {
        $images = [];
        if (isset($config['gallery']) && is_array($config['gallery'])) {
            foreach ($config['gallery'] as $image) {
                if (is_string($image) && $image !== '') {
                    $images[] = $image;
                }
            }
        }

        return $images;
    }

    // hero image filename: cms photo, else the artist row image, else the settings fallback
    private function resolveHeroFile(array $artist, string $slug, array $dance): string
    {
        $context = (string) $dance['artist_detail_photos_context_hero'];

        $heroFile = $this->photosRepository->getFilename($context, $slug);
        if ($heroFile !== null) {
            return $heroFile;
        }
        if (!empty($artist['image'])) {
            return (string) $artist['image'];
        }

        return (string) $dance['artist_detail_hero_fallback'];
    }

    // schedule image filename: cms photo, else a per-slug fallback, else the default
    private function resolveScheduleFile(string $slug, array $dance): string
    {
        $context = (string) $dance['artist_detail_photos_context_schedule'];

        $fallbacks = $dance['artist_detail_schedule_fallbacks'] ?? [];
        if (!is_array($fallbacks)) {
            $fallbacks = [];
        }

        $default = isset($fallbacks['default']) && is_string($fallbacks['default']) ? $fallbacks['default'] : '';

        $file = $this->photosRepository->getFilename($context, $slug);
        if ($file === null) {
            $file = isset($fallbacks[$slug]) && is_string($fallbacks[$slug]) ? $fallbacks[$slug] : $default;
        }

        if ($file === null || $file === '') {
            // if the db/json dropped "default", keep the shipped asset
            $file = $default !== '' ? $default : 'Artist/hardwell6.png';
        }

        return $file;
    }

    private function buildBreadcrumbs(array $artist, array $dance, array $app): array
    {
        $listPath = (string) ($dance['event_detail_list_path'] ?? '/dance');

        return [
            ['label' => $dance['breadcrumb_home_label'], 'url' => $app['home_path']],
            ['label' => $dance['breadcrumb_dance_label'], 'url' => $listPath],
            // all caps to match the event detail venue crumb
            ['label' => strtoupper((string) ($artist['name'] ?? '')), 'url' => null],
        ];
    }

    // profile + album cover images for the music block
    /** @return string[] [profileImage, albumCoverImage] */
    private function resolveMusicImages(string $slug, array $dance, string $imageBase): array
    {
        $context = (string) $dance['artist_detail_photos_context_music'];

        $profileKey = $this->photoSlotKey($dance['artist_detail_music_profile_slots'] ?? [], $slug, 'profile');
        $albumKey = $this->photoSlotKey($dance['artist_detail_music_album_slots'] ?? [], $slug, 'album_cover');

        $profileFile = $this->photosRepository->getFilename($context, $profileKey);
        $profileImage = $imageBase . ($profileFile ?? (string) $dance['artist_detail_music_profile_fallback']);

        $albumFile = $this->photosRepository->getFilename($context, $albumKey);
        $albumImage = $imageBase . ($albumFile ?? (string) $dance['artist_detail_music_album_fallback']);

        return [$profileImage, $albumImage];
    }

    private function resolveGalleryStats(array $config, array $dance): array
    {
        if (isset($config['gallery_stats']) && is_array($config['gallery_stats'])) {
            return $config['gallery_stats'];
        }

        $stats = $dance['artist_detail_gallery_stats_fallback'] ?? null;
        if (is_array($stats)) {
            return $stats;
        }

        // same default as dance_settings when nothing is set
        return [['num' => '—', 'label' => 'PHOTOS'], ['num' => '—', 'label' => 'SHOWS']];
    }

    // hero tagline from the config, otherwise a trimmed-down version of the bio
    private function resolveHeroTagline(array $config, array $artist, array $dance): ?string
    {
        $maxChars = $this->clampInt($dance['artist_detail_hero_tagline_max_chars'] ?? null, 160, 40, 500);

        $tagline = isset($config['hero_tagline']) ? trim((string) $config['hero_tagline']) : '';
        if ($tagline === '') {
            $bio = (string) ($artist['bio'] ?? '');
            if ($bio !== '') {
                // leave room for the … when we cut it short
                $cut = max(1, $maxChars - 3);
                $tagline = mb_strlen($bio) > $maxChars ? mb_substr($bio, 0, $cut) . '…' : $bio;
            }
        }

        return $tagline !== '' ? $tagline : null;
    }

    private function resolveFollowUrl(array $config): ?string
    {
        if (isset($config['follow_url']) && is_string($config['follow_url']) && $config['follow_url'] !== '') {
            return $config['follow_url'];
        }

        return null;
    }

    // read a text value from the config block, or use the default
    private function configText(array $config, string $key, string $default): string
    {
        return isset($config[$key]) ? (string) $config[$key] : $default;
    }

    private function configArrayOrNull(array $config, string $key): ?array
    {
        return isset($config[$key]) && is_array($config[$key]) ? $config[$key] : null;
    }

    private function configArrayOrEmpty(array $config, string $key): array
    {
        return isset($config[$key]) && is_array($config[$key]) ? $config[$key] : [];
    }

    // pick a photo slot key for this slug, falling back to "default" then the given name
    private function photoSlotKey(array $slots, string $slug, string $ifMissing): string
    {
        $value = $ifMissing;
        if (isset($slots[$slug])) {
            $value = $slots[$slug];
        } elseif (isset($slots['default'])) {
            $value = $slots['default'];
        }

        return is_string($value) && $value !== '' ? $value : $ifMissing;
    }

    // dance_settings stores numbers as strings, so clamp them into a sane range
    private function clampInt(mixed $raw, int $fallback, int $min, int $max): int
    {
        if (!is_numeric($raw)) {
            return max($min, min($max, $fallback));
        }

        $number = (int) $raw;

        return max($min, min($max, $number > 0 ? $number : $fallback));
    }
}
