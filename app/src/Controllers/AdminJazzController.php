<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\JazzCmsRepository;
use App\Repositories\JazzSettingsRepository;
use App\Repositories\SettingsRepository;

final class AdminJazzController
{
    private const ADMIN_ROLE_ID = 1;

    private JazzCmsRepository $cms;
    private JazzSettingsRepository $jazzSettings;
    private SettingsRepository $appSettings;

    public function __construct()
    {
        $this->cms = new JazzCmsRepository();
        $this->jazzSettings = new JazzSettingsRepository();
        $this->appSettings = new SettingsRepository();
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

    private function app(): array
    {
        return $this->appSettings->getAll();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $app = $this->app();
        require __DIR__ . '/../Views/Admin/jazz-index.php';
    }

    public function events(): void
    {
        $this->requireAdmin();
        $app = $this->app();
        $events = $this->cms->listJazzEventsForAdmin();
        require __DIR__ . '/../Views/Admin/jazz-events-list.php';
    }

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

    public function newEvent(): void
    {
        $this->requireAdmin();
        $venues = $this->cms->listVenues();
        $app = $this->app();
        $csrf = Csrf::token('admin_jazz_event');
        require __DIR__ . '/../Views/Admin/jazz-event-new.php';
    }

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
            try {
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
            $outPages[$slugKey] = [
                'title' => $t,
                'tagline' => $tag,
                'hero_image' => $heroImg,
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

        if ($slug === '' || $title === '' || $imageFile === '' || $audioFile === '') {
            Session::setFlash('admin_error', 'Slug, title, image file, and audio file are required.');
            header('Location: /admin/jazz/discography/edit?slug=' . rawurlencode($slug));
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
}
