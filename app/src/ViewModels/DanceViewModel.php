<?php

namespace App\ViewModels;

/**
 * View model for the public Dance index.
 * Holds ready-to-render data so the template only loops and prints — no logic in the view.
 */
class DanceViewModel
{
    public function __construct(
        public string $pageTitle,
        public array $appSettings,
        public array $breadcrumbs,
        // hero (read by the festival-hero partial)
        public string $heroTitle,
        public string $heroImage,
        public string $heroSubtitle,
        public string $heroButtonText,
        public string $heroButtonUrl,
        // about section
        public string $aboutHeading,
        public array $aboutParagraphs,
        // featured strip
        public string $featuredTitle,
        public array $featuredCards,
        // all-events tabs
        public string $allEventsTitle,
        public array $dayLabels,
        public array $dayPanels,
        // artists
        public string $artistsTitle,
        public string $artistInfoLabel,
        public string $showMoreLabel,
        public array $artistCards,
    ) {
    }
}
