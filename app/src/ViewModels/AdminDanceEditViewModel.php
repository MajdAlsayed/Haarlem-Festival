<?php

declare(strict_types=1);

namespace App\ViewModels;

final class AdminDanceEditViewModel
{
    public function __construct(
        public string $csrf,
        public string $dancePageTitle,
        public string $aboutSectionHeading,
        public string $featuredSectionTitle,
        public string $allEventsSectionTitle,
        public string $artistsSectionTitle,
        public string $heroCtaLabel,
        public string $heroImage,
        public string $heroSubtitle,
        public string $aboutP1,
        public string $aboutP2,
        public string $aboutP3,
        public string $featuredImagesLines,
        public string $fridayImagesLines,
        public string $saturdayImagesLines,
        public string $sundayImagesLines,
        public string $featuredGenreLabelsLines,
        public string $breadcrumbHomeLabel,
        public string $breadcrumbDanceLabel,
        public string $eventDetailListPath,
        public string $eventDetailPhotosContext,
        public string $eventDetailHeroFallback,
        public string $eventDetailGalleryLines,
        public string $defaultEventDay,
        public string $eventDetailVenueCountry,
        public string $defaultMapLat,
        public string $defaultMapLon,
        public string $venueCoordinatesJson,
        public string $artistDetailDanceImagesBasePath,
        public string $artistDetailPhotosContextHero,
        public string $artistDetailPhotosContextSchedule,
        public string $artistDetailPhotosContextMusic,
        public string $artistDetailHeroFallback,
        public string $artistDetailScheduleFallbacksJson,
        public string $artistDetailMusicProfileSlotsJson,
        public string $artistDetailMusicAlbumSlotsJson,
        public string $artistDetailMusicProfileFallback,
        public string $artistDetailMusicAlbumFallback,
        public string $artistDetailDefaultLocation,
        public string $artistDetailDefaultAlbumTitle,
        public string $artistDetailDefaultAlbumSub,
        public string $artistDetailGalleryTargetCount,
        public string $artistDetailHeroTaglineMaxChars,
        public string $artistDetailGalleryStatsJson,
        public string $uploadCsrf,
        public array $appSettings,
        public ?string $error = null,
        public ?string $success = null
    ) {
    }
}
