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

    public function __construct()
    {
        $this->danceSettingsRepository = new DanceSettingsRepository();
        $this->settingsRepository = new SettingsRepository();
        $this->danceCms = new DanceCmsRepository();
    }

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

    public function events(): void
    {
        $this->requireAdmin();
        $app = $this->settingsRepository->getAll();
        $events = $this->danceCms->listDanceEventsForAdmin();
        require __DIR__ . '/../Views/Admin/dance-events-list.php';
    }

    public function newEvent(): void
    {
        $this->requireAdmin();
        $venues = $this->danceCms->listVenues();
        $app = $this->settingsRepository->getAll();
        $csrf = Csrf::token('admin_dance_event');
        require __DIR__ . '/../Views/Admin/dance-event-new.php';
    }

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
        require __DIR__ . '/../Views/Admin/dance-event-edit.php';
    }

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

    public function artists(): void
    {
        $this->requireAdmin();
        $app = $this->settingsRepository->getAll();
        $merged = $this->danceSettingsRepository->getMergedWithConfig();
        $artists = $merged['artists'] ?? [];
        if (!is_array($artists)) {
            $artists = [];
        }
        require __DIR__ . '/../Views/Admin/dance-artists-list.php';
    }

    public function artistsNew(): void
    {
        $this->requireAdmin();
        $app = $this->settingsRepository->getAll();
        $csrf = Csrf::token('admin_dance_artist');
        $isNew = true;
        $row = ['name' => '', 'slug' => '', 'bio' => '', 'image' => ''];
        require __DIR__ . '/../Views/Admin/dance-artist-edit.php';
    }

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
        require __DIR__ . '/../Views/Admin/dance-artist-edit.php';
    }

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

    private static function isValidArtistSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9-]+$/', $slug);
    }

    /** CMS hub at /admin/dance (dashboard cards → edit form, public site). */
    public function index(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $app = $this->settingsRepository->getAll();
        require __DIR__ . '/../Views/Admin/dance-index.php';
    }

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
            uploadCsrf: Csrf::token('cms_upload'),
            appSettings: $this->settingsRepository->getAll(),
            error: $error,
            success: $success
        );

        require __DIR__ . '/../Views/Admin/DanceEdit.php';
    }

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

        $this->showForm(null, 'Saved.');
    }

    /**
     * @return string[]
     */
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

    private static function looksUnsafeFilename(string $name): bool
    {
        return str_contains($name, '..') || str_contains($name, '/') || str_contains($name, '\\');
    }
}
