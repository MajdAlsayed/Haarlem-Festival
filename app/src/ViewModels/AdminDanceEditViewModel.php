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
        public string $uploadCsrf,
        public array $appSettings,
        public ?string $error = null,
        public ?string $success = null
    ) {
    }
}
