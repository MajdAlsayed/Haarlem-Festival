<?php

declare(strict_types=1);

namespace App\ViewModels;

class DanceViewModel
{
    public function __construct(
        public string $pageTitle,
        public array $appSettings,
        public array $breadcrumbs,
        public string $heroTitle,
        public string $heroImage,
        public string $heroSubtitle,
        public string $heroButtonText,
        public string $heroButtonUrl,
        public string $aboutHeading,
        public array $aboutParagraphs,
        public string $featuredTitle,
        public array $featuredCards,
        public string $allEventsTitle,
        public array $dayLabels,
        public array $dayPanels,
        public string $artistsTitle,
        public string $artistInfoLabel,
        public string $showMoreLabel,
        public array $artistCards,
    ) {
    }

    public static function fromPageData(array $page): self
    {
        $hero = $page['hero'];
        $about = $page['about'];
        $featured = $page['featured'];
        $schedule = $page['schedule'];
        $artists = $page['artists'];

        return new self(
            pageTitle: (string) $page['pageTitle'],
            appSettings: $page['appSettings'],
            breadcrumbs: $page['breadcrumbs'],
            heroTitle: (string) $hero['title'],
            heroImage: (string) $hero['image'],
            heroSubtitle: (string) $hero['subtitle'],
            heroButtonText: (string) $hero['buttonText'],
            heroButtonUrl: (string) $hero['buttonUrl'],
            aboutHeading: (string) $about['heading'],
            aboutParagraphs: $about['paragraphs'],
            featuredTitle: (string) $featured['title'],
            featuredCards: $featured['cards'],
            allEventsTitle: (string) $schedule['title'],
            dayLabels: $schedule['dayLabels'],
            dayPanels: $schedule['panels'],
            artistsTitle: (string) $artists['title'],
            artistInfoLabel: (string) $artists['infoLabel'],
            showMoreLabel: (string) $artists['showMoreLabel'],
            artistCards: $artists['cards'],
        );
    }
}
