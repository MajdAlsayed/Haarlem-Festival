<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Core\HtmlSanitizer;
use App\Core\Session;
use App\Repositories\DanceCmsRepository;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\JazzCmsRepository;
use App\Repositories\SettingsRepository;
use App\ViewModels\AdminDanceEditViewModel;

/** Admin CMS for Dance: page copy (dance_settings), events (dance event type), artists strip (JSON in dance_settings). */
final class AdminDanceController
{
    private const ADMIN_ROLE_ID = 1;

    private DanceSettingsRepository $danceSettingsRepository;
    private SettingsRepository $settingsRepository;
    private DanceCmsRepository $danceCms;

    /** Create admin dance repositories/settings handlers. */
    public function __construct()
    {
        $this->danceSettingsRepository = new DanceSettingsRepository();
        $this->settingsRepository = new SettingsRepository();
        $this->danceCms = new DanceCmsRepository();
    }

    /** Legacy guard for routes that still call local admin checks. */
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

    // —— Dance events (same shape as Jazz admin) ——————————————————————————————

    /** List dance events in admin. */
    public function events(): void
    {
        $this->requireAdmin();
        $app = $this->settingsRepository->getAll();
        $events = $this->danceCms->listDanceEventsForAdmin();
        require __DIR__ . '/../Views/Admin/Dance/dance-events-list.php';
    }

    /** Show create form for a new dance event. */
    public function newEvent(): void
    {
        $this->requireAdmin();
        $venues = $this->danceCms->listVenues();
        $app = $this->settingsRepository->getAll();
        $csrf = Csrf::token('admin_dance_event');
        require __DIR__ . '/../Views/Admin/Dance/dance-event-new.php';
    }

    /** Show edit form for an existing dance event. */
    public function editEvent(): void
    {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: /admin/dance/events');
            exit;
        }
        $event = $this->danceCms->getDanceEventById($id);
        if (!$event) {
            Session::setFlash('admin_error', 'Dance event not found.');
            header('Location: /admin/dance/events');
            exit;
        }
        $venues = $this->danceCms->listVenues();
        $audio = (new JazzCmsRepository())->getEventAudio($id);
        $app = $this->settingsRepository->getAll();
        $csrf = Csrf::token('admin_dance_event');
        require __DIR__ . '/../Views/Admin/Dance/dance-event-edit.php';
    }

    /** Validate and persist dance event create/update. */
    public function saveEvent(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/dance/events');
            exit;
        }
        if (!Csrf::validate('admin_dance_event', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request. Please try again.');
            header('Location: /admin/dance/events');
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
            header('Location: ' . ($eventId > 0 ? '/admin/dance/events/edit?id=' . $eventId : '/admin/dance/events/new'));
            exit;
        }

        $jazzAudio = new JazzCmsRepository();
        try {
            if ($eventId > 0) {
                $this->danceCms->updateDanceEvent(
                    $eventId,
                    $venueId,
                    $title,
                    $description,
                    $eventDay,
                    $startTime,
                    $endTime !== '' ? $endTime : null,
                    $hall !== '' ? $hall : null,
                    $seats,
                    $price
                );
                $newId = $eventId;
            } else {
                $newId = $this->danceCms->createDanceEvent(
                    $venueId,
                    $title,
                    $description,
                    $eventDay,
                    $startTime,
                    $endTime !== '' ? $endTime : null,
                    $hall !== '' ? $hall : null,
                    $seats,
                    $price
                );
            }

            if ($clearAudio) {
                $jazzAudio->deleteEventAudio($newId);
            } elseif ($audioPath !== '') {
                $jazzAudio->upsertEventAudio($newId, $audioPath, $audioTitle !== '' ? $audioTitle : null);
            }
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', 'Could not save event: ' . $e->getMessage());
            header('Location: ' . ($eventId > 0 ? '/admin/dance/events/edit?id=' . $eventId : '/admin/dance/events/new'));
            exit;
        }

        Session::setFlash('admin_success', 'Dance event saved.');
        header('Location: /admin/dance/events/edit?id=' . $newId);
        exit;
    }

    /** Delete a dance event row from admin. */
    public function deleteEvent(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/dance/events');
            exit;
        }
        $form = (string) ($_POST['_csrf_form'] ?? '');
        if ($form === '' || !Csrf::validate($form, $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/dance/events');
            exit;
        }
        $id = (int) ($_POST['event_id'] ?? 0);
        if ($id <= 0) {
            header('Location: /admin/dance/events');
            exit;
        }
        try {
            $this->danceCms->deleteDanceEvent($id);
            Session::setFlash('admin_success', 'Event deleted.');
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', $e->getMessage());
        }
        header('Location: /admin/dance/events');
        exit;
    }

    // —— Artists (homepage strip — JSON in dance_settings['artists']) ——————————

    /** List homepage dance artists from dance_settings JSON. */
    public function artists(): void
    {
        $this->requireAdmin();
        $app = $this->settingsRepository->getAll();
        $merged = $this->danceSettingsRepository->getMergedWithConfig();
        $artists = $merged['artists'] ?? [];
        if (!is_array($artists)) {
            $artists = [];
        }
        require __DIR__ . '/../Views/Admin/Dance/dance-artists-list.php';
    }

    /** Show form for adding a homepage artist card. */
    public function artistsNew(): void
    {
        $this->requireAdmin();
        $app = $this->settingsRepository->getAll();
        $csrf = Csrf::token('admin_dance_artist');
        $isNew = true;
        $row = ['name' => '', 'slug' => '', 'bio' => '', 'image' => ''];
        require __DIR__ . '/../Views/Admin/Dance/dance-artist-edit.php';
    }

    /** Show form for editing a homepage artist card. */
    public function artistsEdit(): void
    {
        $this->requireAdmin();
        $slug = trim((string) ($_GET['slug'] ?? ''));
        if ($slug === '' || !self::isValidArtistSlug($slug)) {
            Session::setFlash('admin_error', 'Invalid artist.');
            header('Location: /admin/dance/artists');
            exit;
        }
        $merged = $this->danceSettingsRepository->getMergedWithConfig();
        $artists = $merged['artists'] ?? [];
        if (!is_array($artists)) {
            $artists = [];
        }
        $row = null;
        foreach ($artists as $a) {
            if (is_array($a) && isset($a['slug']) && (string) $a['slug'] === $slug) {
                $row = [
                    'name' => (string) ($a['name'] ?? ''),
                    'slug' => (string) ($a['slug'] ?? ''),
                    'bio' => (string) ($a['bio'] ?? ''),
                    'image' => (string) ($a['image'] ?? ''),
                ];
                break;
            }
        }
        if ($row === null) {
            Session::setFlash('admin_error', 'Artist not found.');
            header('Location: /admin/dance/artists');
            exit;
        }
        $app = $this->settingsRepository->getAll();
        $csrf = Csrf::token('admin_dance_artist');
        $isNew = false;
        require __DIR__ . '/../Views/Admin/Dance/dance-artist-edit.php';
    }

    /** Validate and save homepage artist cards JSON. */
    public function saveArtist(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/dance/artists');
            exit;
        }
        if (!Csrf::validate('admin_dance_artist', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/dance/artists');
            exit;
        }

        $originalSlug = trim((string) ($_POST['original_slug'] ?? ''));
        $name = trim((string) ($_POST['name'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $bio = trim((string) ($_POST['bio'] ?? ''));
        $image = trim((string) ($_POST['image'] ?? ''));

        $artistFormBack = $originalSlug !== ''
            ? '/admin/dance/artists/edit?slug=' . rawurlencode($originalSlug)
            : '/admin/dance/artists/new';

        if ($name === '' || $slug === '' || !self::isValidArtistSlug($slug)) {
            Session::setFlash('admin_error', 'Name and a valid slug (lowercase letters, numbers, hyphens) are required.');
            header('Location: ' . $artistFormBack);
            exit;
        }
        if ($image !== '' && self::looksUnsafeFilename($image)) {
            Session::setFlash('admin_error', 'Image: filename only, no path characters.');
            header('Location: ' . $artistFormBack);
            exit;
        }

        $merged = $this->danceSettingsRepository->getMergedWithConfig();
        $artists = $merged['artists'] ?? [];
        if (!is_array($artists)) {
            $artists = [];
        }

        $list = [];
        foreach ($artists as $a) {
            if (!is_array($a) || empty($a['slug'])) {
                continue;
            }
            $s = (string) $a['slug'];
            if ($originalSlug !== '' && $s === $originalSlug) {
                continue;
            }
            if ($s === $slug) {
                Session::setFlash('admin_error', 'That slug is already used.');
                header('Location: ' . $artistFormBack);
                exit;
            }
            $list[] = [
                'name' => (string) ($a['name'] ?? ''),
                'slug' => $s,
                'bio' => (string) ($a['bio'] ?? ''),
                'image' => (string) ($a['image'] ?? ''),
            ];
        }

        $list[] = [
            'name' => $name,
            'slug' => $slug,
            'bio' => $bio,
            'image' => $image,
        ];

        $this->danceSettingsRepository->upsertSetting('artists', json_encode(array_values($list)));
        Session::setFlash('admin_success', 'Artist saved.');
        header('Location: /admin/dance/artists');
        exit;
    }

    /** Remove one homepage artist card by slug. */
    public function deleteArtist(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/dance/artists');
            exit;
        }
        $form = (string) ($_POST['_csrf_form'] ?? '');
        if ($form === '' || !Csrf::validate($form, $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/dance/artists');
            exit;
        }
        $delSlug = trim((string) ($_POST['slug'] ?? ''));
        if ($delSlug === '' || !self::isValidArtistSlug($delSlug)) {
            header('Location: /admin/dance/artists');
            exit;
        }
        $merged = $this->danceSettingsRepository->getMergedWithConfig();
        $artists = $merged['artists'] ?? [];
        if (!is_array($artists)) {
            $artists = [];
        }
        $list = [];
        foreach ($artists as $a) {
            if (!is_array($a) || empty($a['slug'])) {
                continue;
            }
            if ((string) $a['slug'] === $delSlug) {
                continue;
            }
            $list[] = [
                'name' => (string) ($a['name'] ?? ''),
                'slug' => (string) ($a['slug'] ?? ''),
                'bio' => (string) ($a['bio'] ?? ''),
                'image' => (string) ($a['image'] ?? ''),
            ];
        }
        $this->danceSettingsRepository->upsertSetting('artists', json_encode(array_values($list)));
        Session::setFlash('admin_success', 'Artist removed from the Dance page.');
        header('Location: /admin/dance/artists');
        exit;
    }

    /** Strict slug rule for URL-safe artist links. */
    private static function isValidArtistSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9-]+$/', $slug);
    }

    /** CMS hub at /admin/dance (dashboard cards → edit form, public site). */
    /** Redirect /admin/dance to the CMS edit form. */
    public function index(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $app = $this->settingsRepository->getAll();
        require __DIR__ . '/../Views/Admin/Dance/dance-index.php';
    }

    /** Render /admin/cms/dance with merged defaults + DB values. */
    public function showForm(?string $error = null, ?string $success = null): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $m = $this->danceSettingsRepository->getMergedWithConfig();
        $defaults = require __DIR__ . '/../Config/dance.php';

        $title = isset($m['dance_page_title']) && is_string($m['dance_page_title']) ? $m['dance_page_title'] : 'Dance Festival';
        $aboutHeading = isset($m['about_section_heading']) && is_string($m['about_section_heading']) ? $m['about_section_heading'] : (string) ($defaults['about_section_heading'] ?? 'About Dance');
        $featTitle = isset($m['featured_section_title']) && is_string($m['featured_section_title']) ? $m['featured_section_title'] : (string) ($defaults['featured_section_title'] ?? 'Featured Events');
        $allTitle = isset($m['all_events_section_title']) && is_string($m['all_events_section_title']) ? $m['all_events_section_title'] : (string) ($defaults['all_events_section_title'] ?? 'All Events');
        $artTitle = isset($m['artists_section_title']) && is_string($m['artists_section_title']) ? $m['artists_section_title'] : (string) ($defaults['artists_section_title'] ?? 'Artist(s)');
        $heroCta = isset($m['hero_cta_label']) && is_string($m['hero_cta_label']) ? $m['hero_cta_label'] : (string) ($defaults['hero_cta_label'] ?? 'View Dance Events');
        $hero = isset($m['hero_image']) && is_string($m['hero_image']) ? $m['hero_image'] : '';
        $sub = isset($m['hero_subtitle']) && is_string($m['hero_subtitle']) ? $m['hero_subtitle'] : '';
        $paras = isset($m['about_paragraphs']) && is_array($m['about_paragraphs']) ? $m['about_paragraphs'] : [];
        $p1 = isset($paras[0]) && is_string($paras[0]) ? $paras[0] : '';
        $p2 = isset($paras[1]) && is_string($paras[1]) ? $paras[1] : '';
        $p3 = isset($paras[2]) && is_string($paras[2]) ? $paras[2] : '';

        $featImg = isset($m['featured_images']) && is_array($m['featured_images']) ? $m['featured_images'] : ($defaults['featured_images'] ?? []);
        $friImg = isset($m['friday_images']) && is_array($m['friday_images']) ? $m['friday_images'] : ($defaults['friday_images'] ?? []);
        $satImg = isset($m['saturday_images']) && is_array($m['saturday_images']) ? $m['saturday_images'] : ($defaults['saturday_images'] ?? []);
        $sunImg = isset($m['sunday_images']) && is_array($m['sunday_images']) ? $m['sunday_images'] : ($defaults['sunday_images'] ?? []);
        $genreLabels = isset($m['featured_genre_labels']) && is_array($m['featured_genre_labels']) ? $m['featured_genre_labels'] : ($defaults['featured_genre_labels'] ?? []);

        $galleryFb = $m['event_detail_gallery_fallbacks'] ?? $defaults['event_detail_gallery_fallbacks'] ?? [];
        if (!is_array($galleryFb)) {
            $galleryFb = [];
        }
        $mapPair = $m['default_map_coordinates'] ?? $defaults['default_map_coordinates'] ?? [52.3813, 4.6368];
        if (!is_array($mapPair)) {
            $mapPair = [52.3813, 4.6368];
        }
        $venueCoords = $m['venue_coordinates'] ?? $defaults['venue_coordinates'] ?? [];
        if (!is_array($venueCoords)) {
            $venueCoords = [];
        }

        $schedFb = $m['artist_detail_schedule_fallbacks'] ?? $defaults['artist_detail_schedule_fallbacks'] ?? [];
        if (!is_array($schedFb)) {
            $schedFb = [];
        }
        $profSlots = $m['artist_detail_music_profile_slots'] ?? $defaults['artist_detail_music_profile_slots'] ?? [];
        if (!is_array($profSlots)) {
            $profSlots = [];
        }
        $albSlots = $m['artist_detail_music_album_slots'] ?? $defaults['artist_detail_music_album_slots'] ?? [];
        if (!is_array($albSlots)) {
            $albSlots = [];
        }
        $galStats = $m['artist_detail_gallery_stats_fallback'] ?? $defaults['artist_detail_gallery_stats_fallback'] ?? [];
        if (!is_array($galStats)) {
            $galStats = [];
        }
        $galTarget = (string) ($m['artist_detail_gallery_target_count'] ?? $defaults['artist_detail_gallery_target_count'] ?? 4);
        $tagMax = (string) ($m['artist_detail_hero_tagline_max_chars'] ?? $defaults['artist_detail_hero_tagline_max_chars'] ?? 160);

        $viewModel = new AdminDanceEditViewModel(
            csrf: Csrf::token('cms_dance'),
            dancePageTitle: $title,
            aboutSectionHeading: $aboutHeading,
            featuredSectionTitle: $featTitle,
            allEventsSectionTitle: $allTitle,
            artistsSectionTitle: $artTitle,
            heroCtaLabel: $heroCta,
            heroImage: $hero,
            heroSubtitle: $sub,
            aboutP1: $p1,
            aboutP2: $p2,
            aboutP3: $p3,
            featuredImagesLines: self::arrayToLines($featImg),
            fridayImagesLines: self::arrayToLines($friImg),
            saturdayImagesLines: self::arrayToLines($satImg),
            sundayImagesLines: self::arrayToLines($sunImg),
            featuredGenreLabelsLines: self::arrayToLines($genreLabels),
            breadcrumbHomeLabel: (string) ($m['breadcrumb_home_label'] ?? $defaults['breadcrumb_home_label'] ?? 'HOME'),
            breadcrumbDanceLabel: (string) ($m['breadcrumb_dance_label'] ?? $defaults['breadcrumb_dance_label'] ?? 'DANCE'),
            eventDetailListPath: (string) ($m['event_detail_list_path'] ?? $defaults['event_detail_list_path'] ?? '/dance'),
            eventDetailPhotosContext: (string) ($m['event_detail_photos_context'] ?? $defaults['event_detail_photos_context'] ?? 'dance_event_detail'),
            eventDetailHeroFallback: (string) ($m['event_detail_hero_fallback'] ?? $defaults['event_detail_hero_fallback'] ?? ''),
            eventDetailGalleryLines: self::arrayToLines($galleryFb),
            defaultEventDay: (string) ($m['default_event_day'] ?? $defaults['default_event_day'] ?? 'friday'),
            eventDetailVenueCountry: (string) ($m['event_detail_venue_country'] ?? $defaults['event_detail_venue_country'] ?? 'Netherlands'),
            defaultMapLat: (string) ($mapPair[0] ?? '52.3813'),
            defaultMapLon: (string) ($mapPair[1] ?? '4.6368'),
            venueCoordinatesJson: json_encode($venueCoords, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}',
            artistDetailDanceImagesBasePath: (string) ($m['dance_images_base_path'] ?? $defaults['dance_images_base_path'] ?? '/images/dance/'),
            artistDetailPhotosContextHero: (string) ($m['artist_detail_photos_context_hero'] ?? $defaults['artist_detail_photos_context_hero'] ?? 'dance_artist_hero'),
            artistDetailPhotosContextSchedule: (string) ($m['artist_detail_photos_context_schedule'] ?? $defaults['artist_detail_photos_context_schedule'] ?? 'dance_artist_schedule'),
            artistDetailPhotosContextMusic: (string) ($m['artist_detail_photos_context_music'] ?? $defaults['artist_detail_photos_context_music'] ?? 'dance_artist_music'),
            artistDetailHeroFallback: (string) ($m['artist_detail_hero_fallback'] ?? $defaults['artist_detail_hero_fallback'] ?? ''),
            artistDetailScheduleFallbacksJson: json_encode($schedFb, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}',
            artistDetailMusicProfileSlotsJson: json_encode($profSlots, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}',
            artistDetailMusicAlbumSlotsJson: json_encode($albSlots, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}',
            artistDetailMusicProfileFallback: (string) ($m['artist_detail_music_profile_fallback'] ?? $defaults['artist_detail_music_profile_fallback'] ?? ''),
            artistDetailMusicAlbumFallback: (string) ($m['artist_detail_music_album_fallback'] ?? $defaults['artist_detail_music_album_fallback'] ?? ''),
            artistDetailDefaultLocation: (string) ($m['artist_detail_default_location'] ?? $defaults['artist_detail_default_location'] ?? 'Netherlands'),
            artistDetailDefaultAlbumTitle: (string) ($m['artist_detail_default_album_title'] ?? $defaults['artist_detail_default_album_title'] ?? 'Featured'),
            artistDetailDefaultAlbumSub: (string) ($m['artist_detail_default_album_sub'] ?? $defaults['artist_detail_default_album_sub'] ?? 'Album'),
            artistDetailGalleryTargetCount: $galTarget,
            artistDetailHeroTaglineMaxChars: $tagMax,
            artistDetailGalleryStatsJson: json_encode($galStats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '[]',
            uploadCsrf: Csrf::token('cms_upload'),
            appSettings: $this->settingsRepository->getAll(),
            error: $error,
            success: $success
        );

        require __DIR__ . '/../Views/Admin/Dance/DanceEdit.php';
    }

    /** Validate and save dance CMS settings (page, event detail, artist detail). */
    public function save(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (!Csrf::validate('cms_dance', $_POST['_csrf'] ?? null)) {
            $this->showForm('Wrong CSRF token.', null);

            return;
        }

        $defaults = require __DIR__ . '/../Config/dance.php';

        $pageTitle = trim((string) ($_POST['dance_page_title'] ?? ''));
        $aboutSectionHeading = trim((string) ($_POST['about_section_heading'] ?? ''));
        $featuredSectionTitle = trim((string) ($_POST['featured_section_title'] ?? ''));
        $allEventsSectionTitle = trim((string) ($_POST['all_events_section_title'] ?? ''));
        $artistsSectionTitle = trim((string) ($_POST['artists_section_title'] ?? ''));
        $heroCtaLabel = trim((string) ($_POST['hero_cta_label'] ?? ''));
        $heroImage = trim((string) ($_POST['hero_image'] ?? ''));
        // Rich text fields go through HtmlPurifier so admins can’t paste script tags.
        $heroSubtitle = HtmlSanitizer::purify((string) ($_POST['hero_subtitle'] ?? ''));
        $p1 = HtmlSanitizer::purify((string) ($_POST['about_p1'] ?? ''));
        $p2 = HtmlSanitizer::purify((string) ($_POST['about_p2'] ?? ''));
        $p3 = HtmlSanitizer::purify((string) ($_POST['about_p3'] ?? ''));

        if ($pageTitle === '' || $heroImage === '') {
            $this->showForm('Title and hero image name required.', null);

            return;
        }

        if (self::looksUnsafeFilename($heroImage)) {
            $this->showForm('Hero image: filename only, no / or ..', null);

            return;
        }

        if (HtmlSanitizer::isEmptyHtml($heroSubtitle)) {
            $this->showForm('Hero subtitle cannot be empty.', null);

            return;
        }

        if (strlen($heroSubtitle) > 100000) {
            $this->showForm('Hero subtitle is too long.', null);

            return;
        }

        if ($aboutSectionHeading === '' || $featuredSectionTitle === '' || $allEventsSectionTitle === '' || $artistsSectionTitle === '' || $heroCtaLabel === '') {
            $this->showForm('Section titles / button label empty.', null);

            return;
        }

        $about = array_values(array_filter([$p1, $p2, $p3], static fn ($s) => is_string($s) && !HtmlSanitizer::isEmptyHtml($s)));
        if ($about === []) {
            $this->showForm('Need at least one about paragraph.', null);

            return;
        }

        foreach ($about as $block) {
            if (strlen($block) > 100000) {
                $this->showForm('An about paragraph is too long.', null);

                return;
            }
        }

        $featured = self::linesToStringArray((string) ($_POST['featured_images_lines'] ?? ''));
        $friday = self::linesToStringArray((string) ($_POST['friday_images_lines'] ?? ''));
        $saturday = self::linesToStringArray((string) ($_POST['saturday_images_lines'] ?? ''));
        $sunday = self::linesToStringArray((string) ($_POST['sunday_images_lines'] ?? ''));
        $genreLabels = self::linesToStringArray((string) ($_POST['featured_genre_labels_lines'] ?? ''));

        if ($featured === []) {
            $featured = $defaults['featured_images'] ?? [];
        }
        if ($friday === []) {
            $friday = $defaults['friday_images'] ?? [];
        }
        if ($saturday === []) {
            $saturday = $defaults['saturday_images'] ?? [];
        }
        if ($sunday === []) {
            $sunday = $defaults['sunday_images'] ?? [];
        }
        if ($genreLabels === []) {
            $genreLabels = $defaults['featured_genre_labels'] ?? [];
        }

        foreach ([
            'Featured card images' => $featured,
            'Friday images' => $friday,
            'Saturday images' => $saturday,
            'Sunday images' => $sunday,
        ] as $label => $arr) {
            foreach ($arr as $fn) {
                if (self::looksUnsafeFilename($fn)) {
                    $this->showForm($label . ': bad filename.', null);

                    return;
                }
            }
        }

        $breadcrumbHome = trim((string) ($_POST['breadcrumb_home_label'] ?? ''));
        $breadcrumbDance = trim((string) ($_POST['breadcrumb_dance_label'] ?? ''));
        $eventListPath = trim((string) ($_POST['event_detail_list_path'] ?? ''));
        $photoCtx = trim((string) ($_POST['event_detail_photos_context'] ?? ''));
        $heroFb = trim((string) ($_POST['event_detail_hero_fallback'] ?? ''));
        $galleryLines = self::linesToStringArray((string) ($_POST['event_detail_gallery_lines'] ?? ''));
        $defGal = $defaults['event_detail_gallery_fallbacks'] ?? ['DetailsPage/2.png', 'DetailsPage/3.png', 'DetailsPage/4.png'];
        while (count($galleryLines) < 3) {
            $galleryLines[] = $defGal[count($galleryLines)] ?? 'DetailsPage/2.png';
        }
        $galleryLines = array_slice($galleryLines, 0, 3);

        $defaultDay = strtolower(trim((string) ($_POST['default_event_day'] ?? '')));
        $venueCountry = trim((string) ($_POST['event_detail_venue_country'] ?? ''));
        $mapLatRaw = trim((string) ($_POST['default_map_lat'] ?? ''));
        $mapLonRaw = trim((string) ($_POST['default_map_lon'] ?? ''));
        $venueJsonRaw = (string) ($_POST['venue_coordinates_json'] ?? '');

        if ($breadcrumbHome === '' || $breadcrumbDance === '') {
            $this->showForm('Event detail: breadcrumb labels required.', null);

            return;
        }
        if ($eventListPath === '' || ($eventListPath[0] ?? '') !== '/') {
            $this->showForm('Event detail: list path must start with /.', null);

            return;
        }
        if ($photoCtx === '' || !preg_match('/^[a-z0-9_]+$/i', $photoCtx)) {
            $this->showForm('Event detail: photos context: letters, numbers, underscores only.', null);

            return;
        }
        if ($heroFb === '' || self::looksUnsafeRelativePath($heroFb)) {
            $this->showForm('Event detail: invalid hero fallback path.', null);

            return;
        }
        foreach ($galleryLines as $fn) {
            if (self::looksUnsafeRelativePath($fn)) {
                $this->showForm('Event detail: invalid gallery fallback path.', null);

                return;
            }
        }
        if ($defaultDay === '' || !preg_match('/^[a-z]+$/', $defaultDay)) {
            $this->showForm('Event detail: default day must be a weekday key (e.g. friday).', null);

            return;
        }
        if ($venueCountry === '') {
            $this->showForm('Event detail: country label required.', null);

            return;
        }
        if (!is_numeric($mapLatRaw) || !is_numeric($mapLonRaw)) {
            $this->showForm('Event detail: default map lat/lon must be numbers.', null);

            return;
        }

        $decodedVc = json_decode($venueJsonRaw, true);
        if (!is_array($decodedVc)) {
            $this->showForm('Event detail: venue coordinates must be valid JSON (object: venue name → [lat, lon]).', null);

            return;
        }
        foreach ($decodedVc as $venue => $pair) {
            if (!is_string($venue) || trim($venue) === '') {
                $this->showForm('Event detail: each venue name in the map must be a non-empty string.', null);

                return;
            }
            if (!is_array($pair) || !isset($pair[0], $pair[1]) || !is_numeric($pair[0]) || !is_numeric($pair[1])) {
                $this->showForm('Event detail: each venue needs [latitude, longitude].', null);

                return;
            }
        }

        $imgBase = trim((string) ($_POST['dance_images_base_path'] ?? ''));
        $aHeroCtx = trim((string) ($_POST['artist_detail_photos_context_hero'] ?? ''));
        $aSchedCtx = trim((string) ($_POST['artist_detail_photos_context_schedule'] ?? ''));
        $aMusicCtx = trim((string) ($_POST['artist_detail_photos_context_music'] ?? ''));
        $aHeroFb = trim((string) ($_POST['artist_detail_hero_fallback'] ?? ''));
        $aSchedJson = (string) ($_POST['artist_detail_schedule_fallbacks_json'] ?? '');
        $aProfSlotsJson = (string) ($_POST['artist_detail_music_profile_slots_json'] ?? '');
        $aAlbSlotsJson = (string) ($_POST['artist_detail_music_album_slots_json'] ?? '');
        $aProfFb = trim((string) ($_POST['artist_detail_music_profile_fallback'] ?? ''));
        $aAlbFb = trim((string) ($_POST['artist_detail_music_album_fallback'] ?? ''));
        $aDefLoc = trim((string) ($_POST['artist_detail_default_location'] ?? ''));
        $aAlbTitle = trim((string) ($_POST['artist_detail_default_album_title'] ?? ''));
        $aAlbSub = trim((string) ($_POST['artist_detail_default_album_sub'] ?? ''));
        $aGalTarget = trim((string) ($_POST['artist_detail_gallery_target_count'] ?? ''));
        $aTagMax = trim((string) ($_POST['artist_detail_hero_tagline_max_chars'] ?? ''));
        $aGalStatsJson = (string) ($_POST['artist_detail_gallery_stats_json'] ?? '');

        if ($imgBase === '' || $imgBase[0] !== '/' || !str_ends_with($imgBase, '/')) {
            $this->showForm('Artist detail: image base path must start and end with / (e.g. /images/dance/).', null);

            return;
        }
        foreach (['Artist hero context' => $aHeroCtx, 'Artist schedule context' => $aSchedCtx, 'Artist music context' => $aMusicCtx] as $label => $ctx) {
            if ($ctx === '' || !preg_match('/^[a-z0-9_]+$/i', $ctx)) {
                $this->showForm($label . ': letters, numbers, underscores only.', null);

                return;
            }
        }
        foreach (['Artist hero fallback' => $aHeroFb, 'Music profile fallback' => $aProfFb, 'Music album fallback' => $aAlbFb] as $label => $p) {
            if ($p === '' || self::looksUnsafeRelativePath($p)) {
                $this->showForm($label . ': invalid path under /images/dance/.', null);

                return;
            }
        }
        if ($aDefLoc === '' || $aAlbTitle === '' || $aAlbSub === '') {
            $this->showForm('Artist detail: location / album labels required.', null);

            return;
        }
        if (!ctype_digit($aGalTarget) || (int) $aGalTarget < 1 || (int) $aGalTarget > 20) {
            $this->showForm('Artist detail: gallery target count must be 1–20.', null);

            return;
        }
        if (!ctype_digit($aTagMax) || (int) $aTagMax < 40 || (int) $aTagMax > 500) {
            $this->showForm('Artist detail: hero tagline max must be 40–500.', null);

            return;
        }

        $decSched = json_decode($aSchedJson, true);
        if (!is_array($decSched) || !isset($decSched['default']) || !is_string($decSched['default'])) {
            $this->showForm('Artist detail: schedule fallbacks JSON must be an object with a "default" path.', null);

            return;
        }
        foreach ($decSched as $k => $path) {
            if (!is_string($k) || $k === '') {
                $this->showForm('Artist detail: schedule fallbacks keys must be non-empty strings (slug or "default").', null);

                return;
            }
            if (!is_string($path) || self::looksUnsafeRelativePath($path)) {
                $this->showForm('Artist detail: schedule fallback path invalid for key "' . $k . '".', null);

                return;
            }
        }

        $decProf = json_decode($aProfSlotsJson, true);
        $decAlb = json_decode($aAlbSlotsJson, true);
        if (!is_array($decProf) || !isset($decProf['default']) || !is_string($decProf['default']) || $decProf['default'] === '') {
            $this->showForm('Artist detail: profile slots JSON needs string "default" (Photos slot key).', null);

            return;
        }
        if (!is_array($decAlb) || !isset($decAlb['default']) || !is_string($decAlb['default']) || $decAlb['default'] === '') {
            $this->showForm('Artist detail: album slots JSON needs string "default" (Photos slot key).', null);

            return;
        }
        foreach (['profile' => $decProf, 'album' => $decAlb] as $label => $slotMap) {
            foreach ($slotMap as $sk => $sv) {
                if (!is_string($sk) || !is_string($sv) || $sv === '' || !preg_match('/^[a-z0-9_]+$/i', $sv)) {
                    $this->showForm('Artist detail: ' . $label . ' slots: keys and values must be non-empty; values = slot keys (a-z, 0-9, _).', null);

                    return;
                }
            }
        }

        $decGalStats = json_decode($aGalStatsJson, true);
        if (!is_array($decGalStats)) {
            $this->showForm('Artist detail: gallery stats must be a JSON array of {num, label}.', null);

            return;
        }
        foreach ($decGalStats as $i => $row) {
            if (!is_array($row) || !isset($row['num'], $row['label']) || !is_string($row['num']) || !is_string($row['label'])) {
                $this->showForm('Artist detail: gallery stats row #' . ($i + 1) . ' needs num + label strings.', null);

                return;
            }
        }

        $repo = $this->danceSettingsRepository;
        $repo->upsertSetting('dance_page_title', $pageTitle);
        $repo->upsertSetting('about_section_heading', $aboutSectionHeading);
        $repo->upsertSetting('featured_section_title', $featuredSectionTitle);
        $repo->upsertSetting('all_events_section_title', $allEventsSectionTitle);
        $repo->upsertSetting('artists_section_title', $artistsSectionTitle);
        $repo->upsertSetting('hero_cta_label', $heroCtaLabel);
        $repo->upsertSetting('hero_image', $heroImage);
        $repo->upsertSetting('hero_subtitle', $heroSubtitle);
        $repo->upsertSetting('about_paragraphs', json_encode($about));
        $repo->upsertSetting('featured_images', json_encode(array_values($featured)));
        $repo->upsertSetting('friday_images', json_encode(array_values($friday)));
        $repo->upsertSetting('saturday_images', json_encode(array_values($saturday)));
        $repo->upsertSetting('sunday_images', json_encode(array_values($sunday)));
        $repo->upsertSetting('featured_genre_labels', json_encode(array_values($genreLabels)));

        $repo->upsertSetting('breadcrumb_home_label', $breadcrumbHome);
        $repo->upsertSetting('breadcrumb_dance_label', $breadcrumbDance);
        $repo->upsertSetting('event_detail_list_path', $eventListPath);
        $repo->upsertSetting('event_detail_photos_context', $photoCtx);
        $repo->upsertSetting('event_detail_hero_fallback', $heroFb);
        $repo->upsertSetting('event_detail_gallery_fallbacks', json_encode(array_values($galleryLines)));
        $repo->upsertSetting('default_event_day', $defaultDay);
        $repo->upsertSetting('event_detail_venue_country', $venueCountry);
        $repo->upsertSetting('default_map_coordinates', json_encode([(float) $mapLatRaw, (float) $mapLonRaw]));
        $repo->upsertSetting('venue_coordinates', json_encode($decodedVc));

        $repo->upsertSetting('dance_images_base_path', $imgBase);
        $repo->upsertSetting('artist_detail_photos_context_hero', $aHeroCtx);
        $repo->upsertSetting('artist_detail_photos_context_schedule', $aSchedCtx);
        $repo->upsertSetting('artist_detail_photos_context_music', $aMusicCtx);
        $repo->upsertSetting('artist_detail_hero_fallback', $aHeroFb);
        $repo->upsertSetting('artist_detail_schedule_fallbacks', json_encode($decSched));
        $repo->upsertSetting('artist_detail_music_profile_slots', json_encode($decProf));
        $repo->upsertSetting('artist_detail_music_album_slots', json_encode($decAlb));
        $repo->upsertSetting('artist_detail_music_profile_fallback', $aProfFb);
        $repo->upsertSetting('artist_detail_music_album_fallback', $aAlbFb);
        $repo->upsertSetting('artist_detail_default_location', $aDefLoc);
        $repo->upsertSetting('artist_detail_default_album_title', $aAlbTitle);
        $repo->upsertSetting('artist_detail_default_album_sub', $aAlbSub);
        $repo->upsertSetting('artist_detail_gallery_target_count', $aGalTarget);
        $repo->upsertSetting('artist_detail_hero_tagline_max_chars', $aTagMax);
        $repo->upsertSetting('artist_detail_gallery_stats_fallback', json_encode(array_values($decGalStats)));

        $this->showForm(null, 'Saved.');
    }

    /**
     * @return string[]
     */
    /** Split textarea lines into a clean string array (trim + drop empty). */
    private static function linesToStringArray(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $out = [];
        foreach ($lines as $line) {
            $t = trim((string) $line);
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return $out;
    }

    /**
     * @param mixed $arr
     */
    /** Join string arrays back to textarea format for admin forms. */
    private static function arrayToLines($arr): string
    {
        if (!is_array($arr)) {
            return '';
        }
        $s = [];
        foreach ($arr as $v) {
            if (is_string($v) && trim($v) !== '') {
                $s[] = trim($v);
            }
        }

        return implode("\n", $s);
    }

    /** Reject filename-only fields that try path traversal or folders. */
    private static function looksUnsafeFilename(string $name): bool
    {
        return str_contains($name, '..') || str_contains($name, '/') || str_contains($name, '\\');
    }

    /** Relative path under /public/images/dance/ (allows one subfolder, blocks .. and absolute paths). */
    private static function looksUnsafeRelativePath(string $path): bool
    {
        if ($path === '' || str_contains($path, '..')) {
            return true;
        }
        if (str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            return true;
        }

        return false;
    }
}
