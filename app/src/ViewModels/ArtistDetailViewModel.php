<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Event;

class ArtistDetailViewModel
{
    public array $artist;
    public array $galleryImages;
    public array $artistEvents;
    public string $heroImage;
    public string $scheduleImage;
    public array $breadcrumbs;
    public array $appSettings;
    public ?array $aboutParagraphs;
    public ?array $careerHighlights;
    public array $musicTracks;
    public array $musicExtraTracks;
    public string $musicDisplayName;
    public string $musicRealName;
    public string $musicLocation;
    public string $albumTitle;
    public string $albumSub;
    public array $galleryStats;
    public string $careerImage;
    public string $profileImage;
    public string $albumCoverImage;
    public ?string $heroTagline;
    public ?string $followUrl;
    public string $heroTitle;
    public string $heroAlt;

    public function __construct(
        array $artist,
        array $galleryImages,
        array $artistEvents,
        string $heroImage,
        string $scheduleImage,
        array $breadcrumbs,
        array $appSettings,
        ?array $aboutParagraphs,
        ?array $careerHighlights,
        array $musicTracks,
        array $musicExtraTracks,
        string $musicDisplayName,
        string $musicRealName,
        string $musicLocation,
        string $albumTitle,
        string $albumSub,
        array $galleryStats,
        string $careerImage,
        string $profileImage,
        string $albumCoverImage,
        ?string $heroTagline = null,
        ?string $followUrl = null,
        string $heroTitle = '',
    ) {
        $this->artist = $artist;
        $this->galleryImages = $galleryImages;
        $this->artistEvents = $artistEvents;
        $this->heroImage = $heroImage;
        $this->scheduleImage = $scheduleImage;
        $this->breadcrumbs = $breadcrumbs;
        $this->appSettings = $appSettings;
        $this->aboutParagraphs = $aboutParagraphs;
        $this->careerHighlights = $careerHighlights;
        $this->musicTracks = $musicTracks;
        $this->musicExtraTracks = $musicExtraTracks;
        $this->musicDisplayName = $musicDisplayName;
        $this->musicRealName = $musicRealName;
        $this->musicLocation = $musicLocation;
        $this->albumTitle = $albumTitle;
        $this->albumSub = $albumSub;
        $this->galleryStats = $galleryStats;
        $this->careerImage = $careerImage;
        $this->profileImage = $profileImage;
        $this->albumCoverImage = $albumCoverImage;
        $this->heroTagline = $heroTagline;
        $this->followUrl = $followUrl;
        $name = isset($artist['name']) ? (string) $artist['name'] : '';
        $this->heroTitle = $heroTitle !== '' ? $heroTitle : ($name !== '' ? $name : 'Dance Artist');
        $this->heroAlt = $this->heroTitle;
    }

    public static function fromPageData(array $page): self
    {
        $hero = $page['hero'];
        $gallery = $page['gallery'];
        $schedule = $page['schedule'];
        $music = $page['music'];
        $about = $page['about'];
        $events = $page['events'];

        return new self(
            artist: $page['artist'],
            galleryImages: $gallery['images'],
            artistEvents: $events['items'],
            heroImage: (string) $hero['image'],
            scheduleImage: (string) $schedule['image'],
            breadcrumbs: $page['breadcrumbs'],
            appSettings: $page['appSettings'],
            aboutParagraphs: $about['paragraphs'],
            careerHighlights: $about['highlights'],
            musicTracks: $music['tracks'],
            musicExtraTracks: $music['extraTracks'],
            musicDisplayName: (string) $music['displayName'],
            musicRealName: (string) $music['realName'],
            musicLocation: (string) $music['location'],
            albumTitle: (string) $music['albumTitle'],
            albumSub: (string) $music['albumSub'],
            galleryStats: $gallery['stats'],
            careerImage: (string) $gallery['careerImage'],
            profileImage: (string) $music['profileImage'],
            albumCoverImage: (string) $music['albumCoverImage'],
            heroTagline: $hero['tagline'],
            followUrl: $hero['followUrl'],
            heroTitle: (string) $hero['title'],
        );
    }

    public function hasFullPage(): bool
    {
        return $this->aboutParagraphs !== null && $this->careerHighlights !== null;
    }
}
