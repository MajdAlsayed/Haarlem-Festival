<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\ArtistServiceInterface;
use App\Contracts\ServiceInterface\DanceArtistServiceInterface;
use App\Contracts\ServiceInterface\DanceSettingsServiceInterface;
use App\Contracts\ServiceInterface\EventServiceInterface;
use App\Contracts\ServiceInterface\PhotosServiceInterface;
use App\Contracts\ServiceInterface\SettingsServiceInterface;
use App\Exceptions\NotFoundException;
use App\Models\Event;
use App\ViewModels\ArtistDetailViewModel;

// /dance/artist/{slug} — photos, schedule, cms copy
class DanceArtistService implements DanceArtistServiceInterface
{
    private const DEFAULT_IMAGE_BASE = '/images/dance/';
    private const DEFAULT_SCHEDULE_IMAGE = 'Artist/hardwell6.png';
    private const DEFAULT_GALLERY_STATS = [
        ['num' => '—', 'label' => 'PHOTOS'],
        ['num' => '—', 'label' => 'SHOWS'],
    ];

    public function __construct(
        private ArtistServiceInterface $artistService,
        private EventServiceInterface $eventService,
        private PhotosServiceInterface $photosService,
        private SettingsServiceInterface $settingsService,
        private DanceSettingsServiceInterface $danceSettingsService,
    ) {
    }

    // /dance/artist/{slug}
    public function buildDetailViewModel(string $slug): ArtistDetailViewModel
    {
        return ArtistDetailViewModel::fromPageData($this->getArtistDetailData($slug));
    }

    // artist row, photos, music bit from cms
    public function getArtistDetailData(string $slug): array
    {
        $artist = $this->loadArtist($slug);
        $app = $this->settingsService->getAll();
        $dance = $this->danceSettingsService->getMergedWithConfig();
        $music = $this->loadMusicConfig($slug);
        $imageBase = $this->imageBasePath($dance);

        return [
            'artist' => $artist,
            'appSettings' => $app,
            'breadcrumbs' => $this->loadBreadcrumbsData($artist, $dance, $app),
            'hero' => $this->loadHeroData($artist, $slug, $music, $dance, $imageBase),
            'gallery' => $this->loadGalleryData($artist, $music, $dance),
            'schedule' => $this->loadScheduleData($slug, $dance, $imageBase),
            'music' => $this->loadMusicBlockData($slug, $music, $dance, $imageBase, $artist),
            'about' => $this->loadAboutData($artist, $music),
            'events' => $this->loadEventsData($artist),
        ];
    }

    private function loadArtist(string $slug): array
    {
        $artist = $this->artistService->getBySlug($slug);
        if (!$artist) {
            throw new NotFoundException('Artist not found');
        }

        return $artist;
    }

    private function loadHeroData(
        array $artist,
        string $slug,
        array $music,
        array $dance,
        string $imageBase,
    ): array {
        $name = $this->arrayText($artist, 'name');

        return [
            'image' => $this->imagePath($imageBase, $this->resolveHeroFile($artist, $slug, $dance)),
            'tagline' => $this->resolveHeroTagline($music, $artist, $dance),
            'followUrl' => $this->resolveFollowUrl($music),
            'title' => $name !== '' ? $name : 'Dance Artist',
        ];
    }

    private function loadGalleryData(array $artist, array $music, array $dance): array
    {
        $images = $this->resolveGallery(
            $this->artistService->getPhotoFilenames($artist['id']),
            $music,
            $dance,
        );

        return [
            'images' => $images,
            'stats' => $this->resolveGalleryStats($music, $dance),
            'careerImage' => $images !== [] ? $images[0] : $this->arrayText($artist, 'image'),
        ];
    }

    private function loadScheduleData(string $slug, array $dance, string $imageBase): array
    {
        return [
            'image' => $this->imagePath($imageBase, $this->resolveScheduleFile($slug, $dance)),
        ];
    }

    private function loadMusicBlockData(
        string $slug,
        array $music,
        array $dance,
        string $imageBase,
        array $artist,
    ): array {
        $name = $this->arrayText($artist, 'name');
        [$profileImage, $albumCoverImage] = $this->resolveMusicImages($slug, $dance, $imageBase);

        return [
            'displayName' => $this->musicConfigText($music, 'display_name', strtoupper($name)),
            'realName' => $this->musicConfigText($music, 'real_name', $name),
            'location' => $this->musicConfigText($music, 'location', $this->settingText($dance, 'artist_detail_default_location')),
            'albumTitle' => $this->musicConfigText($music, 'album_title', $this->settingText($dance, 'artist_detail_default_album_title')),
            'albumSub' => $this->musicConfigText($music, 'album_sub', $this->settingText($dance, 'artist_detail_default_album_sub')),
            'tracks' => $this->musicConfigList($music, 'tracks'),
            'extraTracks' => $this->musicConfigList($music, 'extra_tracks'),
            'profileImage' => $profileImage,
            'albumCoverImage' => $albumCoverImage,
        ];
    }

    private function loadAboutData(array $artist, array $music): array
    {
        $paragraphs = $this->musicConfigListOrNull($music, 'about');
        if ($paragraphs === null && $this->arrayText($artist, 'bio') !== '') {
            $paragraphs = [$this->arrayText($artist, 'bio')];
        }

        return [
            'paragraphs' => $paragraphs,
            'highlights' => $this->musicConfigListOrNull($music, 'highlights'),
        ];
    }

    private function loadEventsData(array $artist): array
    {
        return [
            'items' => $this->danceEventsForArtist($artist),
        ];
    }

    private function loadBreadcrumbsData(array $artist, array $dance, array $app): array
    {
        $listPath = $this->settingTextOr($dance, 'event_detail_list_path', '/dance');

        return [
            ['label' => $dance['breadcrumb_home_label'], 'url' => $app['home_path']],
            ['label' => $dance['breadcrumb_dance_label'], 'url' => $listPath],
            ['label' => strtoupper($this->arrayText($artist, 'name')), 'url' => null],
        ];
    }

    private function loadMusicConfig(string $slug): array
    {
        $config = require __DIR__ . '/../Config/dance.php';
        if (isset($config['artist_music'][$slug]) && is_array($config['artist_music'][$slug])) {
            return $config['artist_music'][$slug];
        }

        return [];
    }

    private function danceEventsForArtist(array $artist): array
    {
        $slug = $this->arrayText($artist, 'slug');
        $searchName = $slug === 'hardwell' ? 'Hardwell' : $this->arrayText($artist, 'name');
        $events = [];

        foreach ($this->eventService->getByCategory('dance') as $event) {
            if ($event->title !== null && stripos($event->title, $searchName) !== false) {
                $events[] = $event;
            }
        }

        return $events;
    }

    private function resolveGallery(array $galleryImages, array $music, array $dance): array
    {
        $configGallery = $this->musicConfigGalleryImages($music);
        $target = $this->clampInt(
            $this->settingText($dance, 'artist_detail_gallery_target_count'),
            4,
            1,
            20,
        );

        if (count($galleryImages) < $target && count($configGallery) >= $target) {
            $merged = array_values(array_unique(array_merge($galleryImages, $configGallery)));

            return count($merged) >= $target
                ? array_slice($merged, 0, $target)
                : array_slice($configGallery, 0, $target);
        }

        return $galleryImages;
    }

    private function musicConfigGalleryImages(array $music): array
    {
        $images = [];
        foreach ($this->musicConfigList($music, 'gallery') as $image) {
            if (is_string($image) && $image !== '') {
                $images[] = $image;
            }
        }

        return $images;
    }

    private function resolveHeroFile(array $artist, string $slug, array $dance): string
    {
        $context = $this->settingText($dance, 'artist_detail_photos_context_hero');
        $heroFile = $this->photosService->getFilename($context, $slug);

        if ($heroFile !== null) {
            return $heroFile;
        }

        $artistImage = $this->arrayText($artist, 'image');
        if ($artistImage !== '') {
            return $artistImage;
        }

        return $this->settingText($dance, 'artist_detail_hero_fallback');
    }

    private function resolveScheduleFile(string $slug, array $dance): string
    {
        $context = $this->settingText($dance, 'artist_detail_photos_context_schedule');
        $fallbacks = $this->settingList($dance, 'artist_detail_schedule_fallbacks');
        $default = isset($fallbacks['default']) && is_string($fallbacks['default']) ? $fallbacks['default'] : '';

        $file = $this->photosService->getFilename($context, $slug);
        if ($file === null) {
            $file = isset($fallbacks[$slug]) && is_string($fallbacks[$slug]) ? $fallbacks[$slug] : $default;
        }

        if ($file === null || $file === '') {
            return $default !== '' ? $default : self::DEFAULT_SCHEDULE_IMAGE;
        }

        return $file;
    }

    private function resolveMusicImages(string $slug, array $dance, string $imageBase): array
    {
        $context = $this->settingText($dance, 'artist_detail_photos_context_music');
        $profileSlots = $this->settingList($dance, 'artist_detail_music_profile_slots');
        $albumSlots = $this->settingList($dance, 'artist_detail_music_album_slots');

        $profileKey = $this->photoSlotKey($profileSlots, $slug, 'profile');
        $albumKey = $this->photoSlotKey($albumSlots, $slug, 'album_cover');

        $profileFile = $this->photosService->getFilename($context, $profileKey);
        $profileName = $profileFile !== null ? $profileFile : $this->settingText($dance, 'artist_detail_music_profile_fallback');

        $albumFile = $this->photosService->getFilename($context, $albumKey);
        $albumName = $albumFile !== null ? $albumFile : $this->settingText($dance, 'artist_detail_music_album_fallback');

        return [
            $this->imagePath($imageBase, $profileName),
            $this->imagePath($imageBase, $albumName),
        ];
    }

    private function resolveGalleryStats(array $music, array $dance): array
    {
        $fromMusic = $this->musicConfigList($music, 'gallery_stats');
        if ($fromMusic !== []) {
            return $fromMusic;
        }

        $fromSettings = $this->settingList($dance, 'artist_detail_gallery_stats_fallback');
        if ($fromSettings !== []) {
            return $fromSettings;
        }

        return self::DEFAULT_GALLERY_STATS;
    }

    private function resolveHeroTagline(array $music, array $artist, array $dance): ?string
    {
        $maxChars = $this->clampInt(
            $this->settingText($dance, 'artist_detail_hero_tagline_max_chars'),
            160,
            40,
            500,
        );
        $tagline = trim($this->musicConfigText($music, 'hero_tagline', ''));

        if ($tagline === '') {
            $bio = $this->arrayText($artist, 'bio');
            if ($bio !== '') {
                $cut = max(1, $maxChars - 3);
                $tagline = mb_strlen($bio) > $maxChars ? mb_substr($bio, 0, $cut) . '…' : $bio;
            }
        }

        return $tagline !== '' ? $tagline : null;
    }

    private function resolveFollowUrl(array $music): ?string
    {
        $url = $this->musicConfigText($music, 'follow_url', '');

        return $url !== '' ? $url : null;
    }

    private function imageBasePath(array $dance): string
    {
        $base = $this->settingTextOr($dance, 'dance_images_base_path', self::DEFAULT_IMAGE_BASE);
        if ($base === '') {
            return self::DEFAULT_IMAGE_BASE;
        }

        return str_ends_with($base, '/') ? $base : $base . '/';
    }

    private function imagePath(string $base, string $filename): string
    {
        if ($filename === '') {
            return '';
        }

        return $base . rawurlencode($filename);
    }

    private function photoSlotKey(array $slots, string $slug, string $fallback): string
    {
        if (isset($slots[$slug]) && is_string($slots[$slug]) && $slots[$slug] !== '') {
            return $slots[$slug];
        }
        if (isset($slots['default']) && is_string($slots['default']) && $slots['default'] !== '') {
            return $slots['default'];
        }

        return $fallback;
    }

    private function clampInt(string $raw, int $fallback, int $min, int $max): int
    {
        if (!is_numeric($raw)) {
            return max($min, min($max, $fallback));
        }

        $number = (int) $raw;

        return max($min, min($max, $number > 0 ? $number : $fallback));
    }

    private function settingTextOr(array $settings, string $key, string $fallback): string
    {
        $value = $this->settingText($settings, $key);

        return $value !== '' ? $value : $fallback;
    }

    private function settingText(array $settings, string $key): string
    {
        if (isset($settings[$key]) && is_string($settings[$key])) {
            return $settings[$key];
        }

        return '';
    }

    private function settingList(array $settings, string $key): array
    {
        if (isset($settings[$key]) && is_array($settings[$key])) {
            return $settings[$key];
        }

        return [];
    }

    private function musicConfigText(array $music, string $key, string $default): string
    {
        if (isset($music[$key])) {
            return (string) $music[$key];
        }

        return $default;
    }

    private function musicConfigList(array $music, string $key): array
    {
        if (isset($music[$key]) && is_array($music[$key])) {
            return $music[$key];
        }

        return [];
    }

    private function musicConfigListOrNull(array $music, string $key): ?array
    {
        if (isset($music[$key]) && is_array($music[$key])) {
            return $music[$key];
        }

        return null;
    }

    private function arrayText(array $row, string $key): string
    {
        return isset($row[$key]) ? (string) $row[$key] : '';
    }
}
