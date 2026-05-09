<?php
declare(strict_types=1);

namespace App\ViewModels;

/**
 * Everything a single jazz artist page needs in one object so the PHP template stays mostly HTML.
 *
 * Slug identifies the artist in URLs and DB tables; events are the schedule rows; discography and bandMembers
 * power the optional blocks below; the two text fields at the end are CMS copy (intro + career highlights HTML).
 */
final class JazzArtistViewModel
{
    /**
     * Another simple DTO for artist templates — all data is prepared in {@see \App\Controllers\JazzController::renderArtist()}.
     *
     * @param array<int, array<string,mixed>> $events
     * @param list<array<string,mixed>> $discography track rows with image_url, audio_url, title, etc.
     * @param list<array{name:string,role:string,img:string}> $bandMembers
     */
    public function __construct(
        public string $slug,
        public string $artistTitle,
        public string $tagline,
        public string $heroImage,
        public string $bio,
        /** @var array<int, array<string,mixed>> $events */
        public array $events,
        /** @var list<array<string,mixed>> */
        public array $discography = [],
        /** @var list<array{name:string,role:string,img:string}> */
        public array $bandMembers = [],
        /** Plain-text intro under the hero (CMS). Empty = fall back to event description or page default. */
        public string $pageIntroText = '',
        /** HTML for career highlights (CMS); sanitize when rendering. Empty = built-in defaults. */
        public string $careerHighlightsHtml = ''
    ) {}
}