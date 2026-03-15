<?php

namespace App\ViewModels;

use App\Models\Event;

/**
 * Data for the Dance artist detail view. The controller fills this; the view only uses these properties.
 */
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
        string $albumCoverImage
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
    }

    public function hasFullPage(): bool
    {
        return $this->aboutParagraphs !== null && $this->careerHighlights !== null;
    }
}
