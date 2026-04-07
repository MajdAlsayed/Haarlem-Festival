<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\JazzRepository;
use App\Repositories\JazzSettingsRepository;
use App\Services\JazzArtistHtmlSanitizer;
use App\Services\JazzCareerHighlightsPlainParser;
use App\ViewModels\JazzArtistViewModel;
use App\ViewModels\JazzViewModel;

/**
 * Everything visitors see under “Jazz” on the site.
 *
 * The main listing is /jazz. Artist pages are things like /jazz/karsu — each one uses the same helper at the bottom
 * (renderArtist) so we don’t copy-paste how we load config, events, discography, and band photos.
 *
 * Where the data comes from:
 * - JazzRepository — events from the database, plus discography and band members for a given artist slug.
 * - JazzSettingsRepository — your jazz “CMS”: it starts from Config/jazz.php and layers admin changes from the DB.
 * - For career highlights we either turn plain text into HTML or sanitize HTML the admin stored.
 */
final class JazzController
{
    private JazzRepository $jazzRepo;
    private JazzSettingsRepository $settingsRepo;

    public function __construct()
    {
        $this->jazzRepo = new JazzRepository();
        $this->settingsRepo = new JazzSettingsRepository();
    }

    /**
     * Jazz homepage. Shows every jazz event as cards, lets people filter by day, and “save to program”
     * (that last part is handled in the browser — see the script at the bottom of jazz-home.php).
     */
    public function index(): void
    {
        $events = $this->jazzRepo->getAll();
        $jazzConfig = $this->settingsRepo->getMergedConfig();
        $eventCardImages = $jazzConfig['event_card_images'] ?? [];
        $allEventsOrder = $jazzConfig['all_events_order'] ?? [];
        $vm = new JazzViewModel($events, 'Jazz Festival', $eventCardImages, $allEventsOrder);

        require __DIR__ . '/../Views/Jazz/jazz-home.php';
    }

    /** Gumbo Kings artist page: discography, schedule, and “Add to program” which adds a real ticket to the cart. */
    public function gumboKings(): void
    {
        $this->renderArtist('gumbo-kings', __DIR__ . '/../Views/Jazz/gumbo-king.php', true);
    }

    /** Karsu’s page: big video-style block, discography carousel, and schedule rows that post to the cart. */
    public function karsu(): void
    {
        $this->renderArtist('karsu', __DIR__ . '/../Views/Jazz/karsu.php');
    }

    /** Gare du Nord — same idea as Gumbo (we can attach preview clips from the database when the flag is true). */
    public function gareDuNord(): void
    {
        $this->renderArtist('gare-du-nord', __DIR__ . '/../Views/Jazz/gare-du-nord.php', true);
    }

    /**
     * One place that wires up every jazz artist page so the three routes stay consistent.
     *
     * Steps in plain English: look up that artist in the merged config (title, tagline, hero, intro text, highlights),
     * find all events whose title matches that artist, optionally glue on preview audio and sort by weekday,
     * pull discography and band members for this slug, then hand everything to the view as a JazzArtistViewModel.
     */
    private function renderArtist(string $slug, string $viewFile, bool $attachPreviewAudio = false): void
    {
        $jazzConfig = $this->settingsRepo->getMergedConfig();
        $artistPages = $jazzConfig['artist_pages'] ?? [];

        $page = $artistPages[$slug] ?? null;
        $pageArr = is_array($page) ? $page : [];
        $title = $pageArr['title'] ?? ucfirst(str_replace('-', ' ', $slug));
        $tagline = $pageArr['tagline'] ?? '';
        $heroFile = $pageArr['hero_image'] ?? 'hero-jazz.jpg';
        $heroImage = '/images/jazz/' . rawurlencode($heroFile);
        $pageIntroText = trim((string) ($pageArr['intro_text'] ?? ''));
        $careerPlain = trim((string) ($pageArr['career_highlights_plain'] ?? ''));
        $careerHtmlStored = trim((string) ($pageArr['career_highlights_html'] ?? ''));
        if ($careerPlain !== '') {
            $generated = JazzCareerHighlightsPlainParser::toHtml($careerPlain, $slug);
            $careerHighlightsHtml = JazzArtistHtmlSanitizer::purifyHighlights($generated);
        } elseif ($careerHtmlStored !== '') {
            $careerHighlightsHtml = JazzArtistHtmlSanitizer::purifyHighlights($careerHtmlStored);
        } else {
            $careerHighlightsHtml = '';
        }

        $events = $this->jazzRepo->getByTitle($title);
        if ($attachPreviewAudio) {
            $events = $this->jazzRepo->attachPreviewAudio($events);
            usort($events, function ($a, $b) {
                $order = ['thursday' => 1, 'friday' => 2, 'saturday' => 3, 'sunday' => 4];
                $da = strtolower((string) ($a['event_day'] ?? ''));
                $db = strtolower((string) ($b['event_day'] ?? ''));
                $oa = $order[$da] ?? 9;
                $ob = $order[$db] ?? 9;
                if ($oa !== $ob) {
                    return $oa <=> $ob;
                }

                return strcmp((string) ($a['start_time'] ?? ''), (string) ($b['start_time'] ?? ''));
            });
        }

        // We don’t have a separate “bio” field for artists — we reuse the first matching event’s description.
        $bio = ($events[0]['description'] ?? '') ?: 'Artist bio placeholder (edit in DB: events.description).';

        $discography = $this->jazzRepo->getDiscographyByArtistSlug($slug);
        $bandMembers = $this->jazzRepo->getBandMembersByArtistSlug($slug);

        $viewModel = new JazzArtistViewModel(
            $slug,
            $title,
            $tagline,
            $heroImage,
            $bio,
            $events,
            $discography,
            $bandMembers,
            $pageIntroText,
            $careerHighlightsHtml
        );

        require $viewFile;
    }
}