<?php

namespace App\Controllers;

use App\Repositories\PhotosRepository;
use App\Repositories\SettingsRepository;
use App\Services\ArtistService;
use App\ViewModels\ArtistDetailViewModel;
use App\Exceptions\NotFoundException;

/**
 * Dance artist detail (/dance/artist/{slug}): repository data plus static copy and tracks from Config/dance.php; CMS images.
 */
class ArtistController
{
    private ArtistService $artistService;
    private PhotosRepository $photosRepository;
    private SettingsRepository $settingsRepository;

    public function __construct()
    {
        $this->artistService = new ArtistService(
            new \App\Repositories\ArtistsRepository(),
            new \App\Repositories\EventRepository()
        );
        $this->photosRepository = new PhotosRepository();
        $this->settingsRepository = new SettingsRepository();
    }

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

        $appSettings = $this->settingsRepository->getAll();
        // Static narrative + Spotify-style blocks live in PHP config so we don’t need extra DB tables for v1.
        $danceConfig = require __DIR__ . '/../Config/dance.php';
        $cfg = isset($danceConfig['artist_music'][$slug]) && is_array($danceConfig['artist_music'][$slug])
            ? $danceConfig['artist_music'][$slug]
            : [];

        $cfgGallery = isset($cfg['gallery']) && is_array($cfg['gallery'])
            ? array_values(array_filter($cfg['gallery'], static fn ($x) => is_string($x) && $x !== ''))
            : [];
        if (count($galleryImages) < 4 && count($cfgGallery) >= 4) {
            $merged = array_values(array_unique(array_merge($galleryImages, $cfgGallery)));
            $galleryImages = count($merged) >= 4 ? array_slice($merged, 0, 4) : array_slice($cfgGallery, 0, 4);
        }

        $heroFilename = $this->photosRepository->getFilename('dance_artist_hero', $slug);
        if ($heroFilename === null) {
            $heroFilename = isset($artist['image']) ? $artist['image'] : 'Artist/hardwell hero.png';
        }
        $heroImage = '/images/dance/' . $heroFilename;

        $scheduleFilename = $this->photosRepository->getFilename('dance_artist_schedule', $slug);
        if ($scheduleFilename === null) {
            $scheduleFilename = ($slug === 'tiesto') ? 'Artist/tiesto3.png' : 'Artist/hardwell6.png';
        }
        $scheduleImage = '/images/dance/' . $scheduleFilename;

        $breadcrumbs = [
            ['label' => 'HOME', 'url' => '/'],
            ['label' => 'DANCE', 'url' => '/dance'],
            ['label' => strtoupper($artist['name'] ?? ''), 'url' => null],
        ];

        $aboutParagraphs = isset($cfg['about']) && is_array($cfg['about']) ? $cfg['about'] : null;
        if ($aboutParagraphs === null && isset($artist['bio']) && $artist['bio'] !== '') {
            $aboutParagraphs = [$artist['bio']];
        }
        $careerHighlights = isset($cfg['highlights']) && is_array($cfg['highlights']) ? $cfg['highlights'] : null;
        $musicTracks = isset($cfg['tracks']) && is_array($cfg['tracks']) ? $cfg['tracks'] : [];
        $musicExtraTracks = isset($cfg['extra_tracks']) && is_array($cfg['extra_tracks']) ? $cfg['extra_tracks'] : [];
        $musicDisplayName = isset($cfg['display_name']) ? $cfg['display_name'] : strtoupper($artist['name'] ?? '');
        $musicRealName = isset($cfg['real_name']) ? $cfg['real_name'] : ($artist['name'] ?? '');
        $musicLocation = isset($cfg['location']) ? $cfg['location'] : 'Netherlands';
        $albumTitle = isset($cfg['album_title']) ? $cfg['album_title'] : 'Featured';
        $albumSub = isset($cfg['album_sub']) ? $cfg['album_sub'] : 'Album';
        $galleryStats = isset($cfg['gallery_stats']) && is_array($cfg['gallery_stats']) ? $cfg['gallery_stats'] : [['num' => '—', 'label' => 'PHOTOS'], ['num' => '—', 'label' => 'SHOWS']];
        $careerImage = isset($galleryImages[0]) ? $galleryImages[0] : ($artist['image'] ?? '');

        $profileKey = ($slug === 'tiesto') ? 'profile_tiesto' : 'profile';
        $albumKey = ($slug === 'tiesto') ? 'album_cover_tiesto' : 'album_cover';
        $profileFilename = $this->photosRepository->getFilename('dance_artist_music', $profileKey);
        $profileImage = ($profileFilename !== null) ? '/images/dance/' . $profileFilename : '/images/dance/Artist/hardwell1.png';
        $albumCoverFilename = $this->photosRepository->getFilename('dance_artist_music', $albumKey);
        $albumCoverImage = ($albumCoverFilename !== null) ? '/images/dance/' . $albumCoverFilename : '/images/dance/Artist/hardwell2.jpg';

        $heroTagline = isset($cfg['hero_tagline']) ? trim((string) $cfg['hero_tagline']) : '';
        if ($heroTagline === '') {
            $bio = (string) ($artist['bio'] ?? '');
            if ($bio !== '') {
                $heroTagline = mb_strlen($bio) > 160 ? mb_substr($bio, 0, 157) . '…' : $bio;
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
            $appSettings,
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
}
