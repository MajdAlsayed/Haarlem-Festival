<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\JazzCmsRepository;
use App\Repositories\JazzSettingsRepository;
use App\Repositories\SettingsRepository;
use App\Services\JazzAdminUploadService;
use App\Services\JazzArtistHtmlSanitizer;

/**
 * Back-office for everything jazz-related.
 *
 * You’ll find the URLs in public/index.php under /admin/jazz/…. This controller talks to the database through
 * JazzCmsRepository (events, audio, discography, band) and JazzSettingsRepository (the big JSON config).
 * File uploads for preview audio and images go through JazzAdminUploadService so paths stay safe and consistent.
 */
final class AdminJazzController
{
    private const ADMIN_ROLE_ID = 1;

    private JazzCmsRepository $cms;
    private JazzSettingsRepository $jazzSettings;
    private SettingsRepository $appSettings;

    /** Hooks up DB writers (jazz CMS), merged JSON settings, and global site settings for the admin chrome. */
    public function __construct()
    {
        $this->cms = new JazzCmsRepository();
        $this->jazzSettings = new JazzSettingsRepository();
        $this->appSettings = new SettingsRepository();
    }

    /** Same gate as other admin screens: must be logged in with the admin role or we bounce you out with a flash. */
    private function requireAdmin(): void
    {
        $auth = $_SESSION['auth'] ?? null;
        if (!$auth || empty($auth['user_id'])) {
            Session::setFlash('login_error', 'Please log in to access the admin area.');
            header('Location: /login');
            exit;
        }
        if ((int) ($auth['role_id'] ?? 0) !== self::ADMIN_ROLE_ID) {
            Session::setFlash('login_error', 'You do not have permission to access the admin area.');
            header('Location: /');
            exit;
        }
    }

    /** Site name, CSS version, etc. — whatever SettingsRepository exposes for the layout. */
    private function app(): array
    {
        return $this->appSettings->getAll();
    }

    /** Landing tile screen at /admin/jazz with links into events, JSON settings, discography, and band members. */
    public function index(): void
    {
        $this->requireAdmin();
        $app = $this->app();
        require __DIR__ . '/../Views/Admin/jazz-index.php';
    }

    // -------------------------------------------------------------------------
    // Jazz events — normal CRUD on the `events` table (only rows whose type is “jazz”), plus optional preview audio.
    // -------------------------------------------------------------------------

    /** Table of all jazz slots in `events` (typed as jazz) for quick edit/delete. */
    public function events(): void
    {
        $this->requireAdmin();
        $app = $this->app();
        $events = $this->cms->listJazzEventsForAdmin();
        require __DIR__ . '/../Views/Admin/jazz-events-list.php';
    }

    /** Edit form for one existing jazz event, including optional preview-audio row from `event_audio`. */
    public function editEvent(): void
    {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: /admin/jazz/events');
            exit;
        }
        $event = $this->cms->getJazzEventById($id);
        if (!$event) {
            Session::setFlash('admin_error', 'Jazz event not found.');
            header('Location: /admin/jazz/events');
            exit;
        }
        $venues = $this->cms->listVenues();
        $audio = $this->cms->getEventAudio($id);
        $app = $this->app();
        $csrf = Csrf::token('admin_jazz_event');
        require __DIR__ . '/../Views/Admin/jazz-event-edit.php';
    }

    /** Blank form to add a new jazz show (same fields as edit, no `event_id` yet). */
    public function newEvent(): void
    {
        $this->requireAdmin();
        $venues = $this->cms->listVenues();
        $app = $this->app();
        $csrf = Csrf::token('admin_jazz_event');
        require __DIR__ . '/../Views/Admin/jazz-event-new.php';
    }

    /**
     * Handles the “Save” button from new/edit event. Validates CSRF, saves the event row, then optionally
     * saves or clears the short preview audio clip people hear on some artist pages.
     */
    public function saveEvent(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/jazz/events');
            exit;
        }
        if (!Csrf::validate('admin_jazz_event', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request. Please try again.');
            header('Location: /admin/jazz/events');
            exit;
        }

        $eventId = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
        $venueId = (int) ($_POST['venue_id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $eventDay = trim((string) ($_POST['event_day'] ?? 'friday'));
        $startTime = trim((string) ($_POST['start_time'] ?? ''));
        $endTime = trim((string) ($_POST['end_time'] ?? ''));
        $hall = trim((string) ($_POST['hall'] ?? ''));
        $seatsRaw = trim((string) ($_POST['seats'] ?? ''));
        $seats = $seatsRaw === '' ? null : (int) $seatsRaw;
        $priceRaw = trim((string) ($_POST['price'] ?? ''));
        $price = $priceRaw === '' ? null : $priceRaw;

        $audioPath = trim((string) ($_POST['preview_audio_path'] ?? ''));
        $audioTitle = trim((string) ($_POST['preview_audio_title'] ?? ''));
        $clearAudio = !empty($_POST['clear_preview_audio']);

        if ($title === '' || $venueId <= 0 || $startTime === '') {
            Session::setFlash('admin_error', 'Title, venue, and start time are required.');
            header('Location: ' . ($eventId > 0 ? '/admin/jazz/events/edit?id=' . $eventId : '/admin/jazz/events/new'));
            exit;
        }

        $uploader = new JazzAdminUploadService();
        try {
            $uploadedPreview = $uploader->storePreviewAudio(
                isset($_FILES['preview_audio_upload']) && is_array($_FILES['preview_audio_upload'])
                    ? $_FILES['preview_audio_upload']
                    : null
            );
            if ($uploadedPreview !== null) {
                $audioPath = $uploadedPreview;
            }
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            Session::setFlash('admin_error', $e->getMessage());
            header('Location: ' . ($eventId > 0 ? '/admin/jazz/events/edit?id=' . $eventId : '/admin/jazz/events/new'));
            exit;
        }

        try {
            if ($eventId > 0) {
                $this->cms->updateJazzEvent(
                    $eventId,
                    $venueId,
                    $title,
                    $description,
                    $eventDay,
                    $startTime,
                    $endTime ?: null,
                    $hall ?: null,
                    $seats,
                    $price
                );
                $newId = $eventId;
            } else {
                $newId = $this->cms->createJazzEvent(
                    $venueId,
                    $title,
                    $description,
                    $eventDay,
                    $startTime,
                    $endTime ?: null,
                    $hall ?: null,
                    $seats,
                    $price
                );
            }

            if ($clearAudio) {
                $this->cms->deleteEventAudio($newId);
            } elseif ($audioPath !== '') {
                $this->cms->upsertEventAudio($newId, $audioPath, $audioTitle !== '' ? $audioTitle : null);
            }
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', 'Could not save event: ' . $e->getMessage());
            header('Location: ' . ($eventId > 0 ? '/admin/jazz/events/edit?id=' . $eventId : '/admin/jazz/events/new'));
            exit;
        }

        Session::setFlash('admin_success', 'Jazz event saved.');
        header('Location: /admin/jazz/events/edit?id=' . $newId);
        exit;
    }

    /** POST-only delete for a jazz event (CSRF tied to the list form). */
    public function deleteEvent(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/jazz/events');
            exit;
        }
        $form = (string) ($_POST['_csrf_form'] ?? '');
        if ($form === '' || !Csrf::validate($form, $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/jazz/events');
            exit;
        }
        $id = (int) ($_POST['event_id'] ?? 0);
        if ($id <= 0) {
            header('Location: /admin/jazz/events');
            exit;
        }
        try {
            $this->cms->deleteJazzEvent($id);
            Session::setFlash('admin_success', 'Event deleted.');
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', $e->getMessage());
        }
        header('Location: /admin/jazz/events');
        exit;
    }

    // -------------------------------------------------------------------------
    // Site-wide jazz “content” that isn’t a single event: hero image, card order, artist page text, image filenames…
    // Stored as JSON rows in table `jazz_settings` and merged on top of Config/jazz.php when the site runs.
    // -------------------------------------------------------------------------

    /**
     * Big jazz “site content” editor: GET shows merged config; POST saves JSON keys after optional image uploads
     * and parses artist pages / sort lists / card image map from the form fields.
     */
    public function settings(): void
    {
        $this->requireAdmin();
        $app = $this->app();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate('admin_jazz_settings', $_POST['_csrf'] ?? null)) {
                Session::setFlash('admin_error', 'Invalid request.');
                header('Location: /admin/jazz/settings');
                exit;
            }
            $config = $this->jazzSettings->getMergedConfig();
            $uploader = new JazzAdminUploadService();
            try {
                $this->applyJazzSettingsUploads($uploader, $_POST, $_FILES, $config);
                $payload = $this->parseSettingsFromPost($_POST, $config);
                $this->jazzSettings->saveMany($payload);
                Session::setFlash('admin_success', 'Jazz settings saved.');
            } catch (\Throwable $e) {
                Session::setFlash('admin_error', 'Could not save: ' . $e->getMessage());
            }
            header('Location: /admin/jazz/settings');
            exit;
        }

        $config = $this->jazzSettings->getMergedConfig();
        $csrf = Csrf::token('admin_jazz_settings');
        $success = Session::getFlash('admin_success');
        $error = Session::getFlash('admin_error');
        require __DIR__ . '/../Views/Admin/jazz-settings.php';
    }

    /**
     * Moves any uploaded hero, placeholder, or per-artist hero files into public/images/jazz/… and patches `$post`
     * so `parseSettingsFromPost` sees the new filenames.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     * @param array<string, mixed> $config
     */
    private function applyJazzSettingsUploads(JazzAdminUploadService $uploader, array &$post, array $files, array $config): void
    {
        $hero = $uploader->storeLayoutImage(
            isset($files['hero_image_upload']) && is_array($files['hero_image_upload']) ? $files['hero_image_upload'] : null,
            'hero'
        );
        if ($hero !== null) {
            $post['hero_image'] = $hero;
        }

        $ph = $uploader->storeLayoutImage(
            isset($files['placeholder_card_upload']) && is_array($files['placeholder_card_upload'])
                ? $files['placeholder_card_upload']
                : null,
            'placeholder'
        );
        if ($ph !== null) {
            $post['placeholder_card'] = $ph;
        }

        $artistPages = is_array($config['artist_pages'] ?? null) ? $config['artist_pages'] : [];
        foreach (array_keys($artistPages) as $slug) {
            $slugKey = preg_replace('/[^a-z0-9\-]/', '', (string) $slug) ?? '';
            if ($slugKey === '') {
                continue;
            }
            $field = 'ap_hero_upload_' . $slugKey;
            $raw = $files[$field] ?? null;
            $up = $uploader->storeLayoutImage(is_array($raw) ? $raw : null, 'artist-' . preg_replace('/[^a-z0-9]+/i', '', $slugKey));
            if ($up !== null) {
                $post['ap_hero_' . $slugKey] = $up;
            }
        }
    }

    /**
     * Turns the settings form into the array `JazzSettingsRepository::saveMany` expects: hero filenames, artist page blocks,
     * weekday sort lists, “all events” order, and the title→image map for cards. Plain highlights win over raw HTML.
     *
     * @param array<string, string> $post
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function parseSettingsFromPost(array $post, array $config): array
    {
        $hero = trim((string) ($post['hero_image'] ?? ''));
        $placeholder = trim((string) ($post['placeholder_card'] ?? ''));
        if ($hero === '' || $placeholder === '') {
            throw new \InvalidArgumentException('Hero and placeholder image filenames are required.');
        }

        $artistPages = is_array($config['artist_pages'] ?? null) ? $config['artist_pages'] : [];
        $outPages = [];
        foreach (array_keys($artistPages) as $slug) {
            $slugKey = preg_replace('/[^a-z0-9\-]/', '', $slug) ?? '';
            if ($slugKey === '') {
                continue;
            }
            $t = trim((string) ($post['ap_title_' . $slugKey] ?? ''));
            $tag = trim((string) ($post['ap_tagline_' . $slugKey] ?? ''));
            $heroImg = trim((string) ($post['ap_hero_' . $slugKey] ?? ''));
            if ($t === '' || $heroImg === '') {
                throw new \InvalidArgumentException("Artist page \"{$slugKey}\" needs title and hero image.");
            }
            $prev = is_array($artistPages[$slug] ?? null) ? $artistPages[$slug] : [];
            $keyIntro = 'ap_intro_' . $slugKey;
            $keyPlain = 'ap_highlights_plain_' . $slugKey;
            $keyHigh = 'ap_highlights_' . $slugKey;
            $intro = array_key_exists($keyIntro, $post)
                ? trim((string) $post[$keyIntro])
                : trim((string) ($prev['intro_text'] ?? ''));
            $highlightsPlain = array_key_exists($keyPlain, $post)
                ? trim((string) $post[$keyPlain])
                : trim((string) ($prev['career_highlights_plain'] ?? ''));
            $highlightsHtmlRaw = array_key_exists($keyHigh, $post)
                ? trim((string) $post[$keyHigh])
                : trim((string) ($prev['career_highlights_html'] ?? ''));

            if ($highlightsPlain !== '') {
                $careerPlainOut = $highlightsPlain;
                $careerHtmlOut = '';
            } else {
                $careerPlainOut = '';
                $careerHtmlOut = JazzArtistHtmlSanitizer::purifyHighlights($highlightsHtmlRaw);
            }

            $outPages[$slugKey] = [
                'title' => $t,
                'tagline' => $tag,
                'hero_image' => $heroImg,
                'intro_text' => $intro,
                'career_highlights_plain' => $careerPlainOut,
                'career_highlights_html' => $careerHtmlOut,
            ];
        }

        $parseLines = static function (string $raw): array {
            $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
            $out = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $out[] = $line;
                }
            }

            return $out;
        };

        $parseMap = static function (string $raw): array {
            $map = [];
            $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                if (str_contains($line, '|')) {
                    [$k, $v] = array_map('trim', explode('|', $line, 2));
                } elseif (str_contains($line, '=>')) {
                    [$k, $v] = array_map('trim', explode('=>', $line, 2));
                } else {
                    continue;
                }
                if ($k !== '' && $v !== '') {
                    $map[$k] = $v;
                }
            }

            return $map;
        };

        $cardRaw = trim((string) ($post['event_card_images'] ?? ''));
        $thu = trim((string) ($post['thursday_order'] ?? ''));
        $fri = trim((string) ($post['friday_order'] ?? ''));
        $sat = trim((string) ($post['saturday_order'] ?? ''));
        $sun = trim((string) ($post['sunday_order'] ?? ''));
        $all = trim((string) ($post['all_events_order'] ?? ''));

        return [
            'hero_image' => $hero,
            'placeholder_card' => $placeholder,
            'artist_pages' => $outPages,
            'thursday_order' => $thu === '' ? ($config['thursday_order'] ?? []) : $parseLines($thu),
            'friday_order' => $fri === '' ? ($config['friday_order'] ?? []) : $parseLines($fri),
            'saturday_order' => $sat === '' ? ($config['saturday_order'] ?? []) : $parseLines($sat),
            'sunday_order' => $sun === '' ? ($config['sunday_order'] ?? []) : $parseLines($sun),
            'all_events_order' => $all === '' ? ($config['all_events_order'] ?? []) : $parseLines($all),
            'event_card_images' => $cardRaw === '' ? ($config['event_card_images'] ?? []) : $parseMap($cardRaw),
        ];
    }

    // -------------------------------------------------------------------------
    // Discography tracks per artist (table artist_discography, keyed by slug like “gumbo-kings”).
    // -------------------------------------------------------------------------

    /** Pick an artist slug (tab) and list their album tracks for edit/delete links. */
    public function discography(): void
    {
        $this->requireAdmin();
        $app = $this->app();
        $defaults = require __DIR__ . '/../Config/jazz.php';
        /** @var array<string, array{title:string,tagline:string,hero_image:string}> $artistPages */
        $artistPages = $defaults['artist_pages'] ?? [];
        $slugs = array_keys($artistPages);
        foreach ($this->cms->listDiscographySlugs() as $s) {
            if (!in_array($s, $slugs, true)) {
                $slugs[] = $s;
            }
        }
        sort($slugs);
        if ($slugs === []) {
            $slugs = ['karsu'];
        }

        $slug = isset($_GET['slug']) ? strtolower(trim((string) $_GET['slug'])) : $slugs[0];
        if ($slug === '') {
            $slug = 'karsu';
        }

        $tracks = $this->cms->listDiscographyBySlug($slug);
        require __DIR__ . '/../Views/Admin/jazz-discography.php';
    }

    /** Add or edit one discography row (?slug= for new, ?id= for existing). */
    public function editDiscTrack(): void
    {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $slug = isset($_GET['slug']) ? strtolower(trim((string) $_GET['slug'])) : '';

        if ($id > 0) {
            $track = $this->cms->getDiscographyTrack($id);
            if (!$track) {
                Session::setFlash('admin_error', 'Track not found.');
                header('Location: /admin/jazz/discography');
                exit;
            }
            $slug = (string) $track['artist_slug'];
        } else {
            $track = null;
            if ($slug === '') {
                header('Location: /admin/jazz/discography');
                exit;
            }
        }

        $app = $this->app();
        $csrf = Csrf::token('admin_jazz_disc');
        require __DIR__ . '/../Views/Admin/jazz-discography-edit.php';
    }

    /** POST handler: optional cover/audio uploads, then insert or update `artist_discography`. */
    public function saveDiscTrack(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/jazz/discography');
            exit;
        }
        if (!Csrf::validate('admin_jazz_disc', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/jazz/discography');
            exit;
        }

        $trackId = (int) ($_POST['track_id'] ?? 0);
        $slug = strtolower(trim((string) ($_POST['artist_slug'] ?? '')));
        $title = trim((string) ($_POST['title'] ?? ''));
        $imageFile = trim((string) ($_POST['image_file'] ?? ''));
        $audioFile = trim((string) ($_POST['audio_file'] ?? ''));
        $releaseYear = trim((string) ($_POST['release_year'] ?? ''));
        $duration = trim((string) ($_POST['duration_seconds'] ?? ''));
        $playCount = (int) ($_POST['play_count'] ?? 0);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $uploader = new JazzAdminUploadService();
        try {
            $imgUp = $uploader->storeDiscographyCover(
                isset($_FILES['disc_image_upload']) && is_array($_FILES['disc_image_upload'])
                    ? $_FILES['disc_image_upload']
                    : null
            );
            if ($imgUp !== null) {
                $imageFile = $imgUp;
            }
            $audUp = $uploader->storeDiscographyTrackAudio(
                isset($_FILES['disc_audio_upload']) && is_array($_FILES['disc_audio_upload'])
                    ? $_FILES['disc_audio_upload']
                    : null
            );
            if ($audUp !== null) {
                $audioFile = $audUp;
            }
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            Session::setFlash('admin_error', $e->getMessage());
            header('Location: ' . ($trackId > 0
                ? '/admin/jazz/discography/edit?id=' . $trackId
                : '/admin/jazz/discography/edit?slug=' . rawurlencode($slug !== '' ? $slug : 'karsu')));
            exit;
        }

        $existing = $trackId > 0 ? $this->cms->getDiscographyTrack($trackId) : null;
        if ($imageFile === '' && is_array($existing)) {
            $imageFile = (string) ($existing['image_file'] ?? '');
        }
        if ($audioFile === '' && is_array($existing)) {
            $audioFile = (string) ($existing['audio_file'] ?? '');
        }

        if ($slug === '' || $title === '' || $imageFile === '' || $audioFile === '') {
            Session::setFlash(
                'admin_error',
                'Slug and title are required. Add cover and audio using the file uploads and/or the path fields (when editing, leave a path blank only if you upload a replacement file).'
            );
            header('Location: ' . ($trackId > 0
                ? '/admin/jazz/discography/edit?id=' . $trackId
                : '/admin/jazz/discography/edit?slug=' . rawurlencode($slug !== '' ? $slug : 'karsu')));
            exit;
        }

        $ry = $releaseYear === '' ? null : (int) $releaseYear;
        $dur = $duration === '' ? null : (int) $duration;

        try {
            if ($trackId > 0) {
                $this->cms->updateDiscographyTrack($trackId, $slug, $title, $ry, $dur, $playCount, $imageFile, $audioFile, $sortOrder);
            } else {
                $this->cms->insertDiscographyTrack($slug, $title, $ry, $dur, $playCount, $imageFile, $audioFile, $sortOrder);
            }
            Session::setFlash('admin_success', 'Discography track saved.');
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', $e->getMessage());
        }
        header('Location: /admin/jazz/discography?slug=' . rawurlencode($slug));
        exit;
    }

    /** POST-only removal of one track; redirects back to the discography list for that slug. */
    public function deleteDiscTrack(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/jazz/discography');
            exit;
        }
        $form = (string) ($_POST['_csrf_form'] ?? '');
        if ($form === '' || !Csrf::validate($form, $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/jazz/discography');
            exit;
        }
        $id = (int) ($_POST['track_id'] ?? 0);
        $slug = (string) ($_POST['return_slug'] ?? '');
        if ($id > 0) {
            $this->cms->deleteDiscographyTrack($id);
            Session::setFlash('admin_success', 'Track removed.');
        }
        header('Location: /admin/jazz/discography?slug=' . rawurlencode($slug));
        exit;
    }

    // -------------------------------------------------------------------------
    // Band lineup photos and captions (table jazz_band_members), again per artist slug.
    // -------------------------------------------------------------------------

    /** Same pattern as discography: choose slug, see everyone in `jazz_band_members` for that artist. */
    public function bandMembers(): void
    {
        $this->requireAdmin();
        $app = $this->app();
        $defaults = require __DIR__ . '/../Config/jazz.php';
        /** @var array<string, mixed> $artistPages */
        $artistPages = $defaults['artist_pages'] ?? [];
        $slugs = array_keys($artistPages);
        foreach ($this->cms->listBandMemberSlugs() as $s) {
            if (!in_array($s, $slugs, true)) {
                $slugs[] = $s;
            }
        }
        sort($slugs);
        if ($slugs === []) {
            $slugs = ['gumbo-kings', 'gare-du-nord'];
        }

        $slug = isset($_GET['slug']) ? strtolower(trim((string) $_GET['slug'])) : $slugs[0];
        if ($slug === '') {
            $slug = 'gumbo-kings';
        }

        $members = $this->cms->listBandMembersBySlug($slug);
        require __DIR__ . '/../Views/Admin/jazz-band-members.php';
    }

    /** Add or edit one band member (?slug= new, ?id= edit). */
    public function editBandMember(): void
    {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $slug = isset($_GET['slug']) ? strtolower(trim((string) $_GET['slug'])) : '';

        if ($id > 0) {
            $member = $this->cms->getBandMember($id);
            if (!$member) {
                Session::setFlash('admin_error', 'Band member not found.');
                header('Location: /admin/jazz/band-members');
                exit;
            }
            $slug = (string) $member['artist_slug'];
        } else {
            $member = null;
            if ($slug === '') {
                header('Location: /admin/jazz/band-members');
                exit;
            }
        }

        $app = $this->app();
        $csrf = Csrf::token('admin_jazz_band');
        require __DIR__ . '/../Views/Admin/jazz-band-member-edit.php';
    }

    /** POST handler: optional photo upload, then insert or update `jazz_band_members`. */
    public function saveBandMember(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/jazz/band-members');
            exit;
        }
        if (!Csrf::validate('admin_jazz_band', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/jazz/band-members');
            exit;
        }

        $memberId = (int) ($_POST['member_id'] ?? 0);
        $slug = strtolower(trim((string) ($_POST['artist_slug'] ?? '')));
        $name = trim((string) ($_POST['name'] ?? ''));
        $role = trim((string) ($_POST['role'] ?? ''));
        $imageFile = trim((string) ($_POST['image_file'] ?? ''));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $uploader = new JazzAdminUploadService();
        try {
            $imgUp = $uploader->storeBandMemberPhoto(
                isset($_FILES['band_photo_upload']) && is_array($_FILES['band_photo_upload'])
                    ? $_FILES['band_photo_upload']
                    : null
            );
            if ($imgUp !== null) {
                $imageFile = $imgUp;
            }
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            Session::setFlash('admin_error', $e->getMessage());
            header('Location: ' . ($memberId > 0
                ? '/admin/jazz/band-members/edit?id=' . $memberId
                : '/admin/jazz/band-members/edit?slug=' . rawurlencode($slug !== '' ? $slug : 'gumbo-kings')));
            exit;
        }

        $existing = $memberId > 0 ? $this->cms->getBandMember($memberId) : null;
        if ($imageFile === '' && is_array($existing)) {
            $imageFile = (string) ($existing['image_file'] ?? '');
        }

        if ($slug === '' || $name === '' || $role === '' || $imageFile === '') {
            Session::setFlash(
                'admin_error',
                'Slug, name, role, and photo are required. Upload an image and/or enter a path under images/jazz/ (when editing, leave path blank only if you upload a new photo).'
            );
            header('Location: ' . ($memberId > 0
                ? '/admin/jazz/band-members/edit?id=' . $memberId
                : '/admin/jazz/band-members/edit?slug=' . rawurlencode($slug !== '' ? $slug : 'gumbo-kings')));
            exit;
        }

        try {
            if ($memberId > 0) {
                $this->cms->updateBandMember($memberId, $slug, $name, $role, $imageFile, $sortOrder);
            } else {
                $this->cms->insertBandMember($slug, $name, $role, $imageFile, $sortOrder);
            }
            Session::setFlash('admin_success', 'Band member saved.');
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', $e->getMessage());
        }
        header('Location: /admin/jazz/band-members?slug=' . rawurlencode($slug));
        exit;
    }

    /** POST-only delete for one band member row. */
    public function deleteBandMember(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/jazz/band-members');
            exit;
        }
        $form = (string) ($_POST['_csrf_form'] ?? '');
        if ($form === '' || !Csrf::validate($form, $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/jazz/band-members');
            exit;
        }
        $id = (int) ($_POST['member_id'] ?? 0);
        $slug = (string) ($_POST['return_slug'] ?? '');
        if ($id > 0) {
            $this->cms->deleteBandMember($id);
            Session::setFlash('admin_success', 'Band member removed.');
        }
        header('Location: /admin/jazz/band-members?slug=' . rawurlencode($slug));
        exit;
    }
}
