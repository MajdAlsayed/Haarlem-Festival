<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Repositories\DanceCmsRepository;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\JazzCmsRepository;
use App\Repositories\SettingsRepository;
use App\Services\AdminDanceService;
use App\Services\DanceSettingsService;
use App\Services\SettingsService;

// dance cms + events + artists admin
final class AdminDanceController
{
    private AdminDanceService $adminDance;

    public function __construct()
    {
        $this->adminDance = new AdminDanceService(
            new DanceCmsRepository(),
            new DanceSettingsService(new DanceSettingsRepository()),
            new SettingsService(new SettingsRepository()),
            new JazzCmsRepository(),
        );
    }

    // dance events

    public function events(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
        $app = $this->adminDance->appSettings();
        $events = $this->adminDance->listEventsForAdmin();
        require __DIR__ . '/../Views/Admin/Dance/dance-events-list.php';
    }

    public function newEvent(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
        $venues = $this->adminDance->listVenues();
        $app = $this->adminDance->appSettings();
        $csrf = Csrf::token('admin_dance_event');
        require __DIR__ . '/../Views/Admin/Dance/dance-event-new.php';
    }

    public function editEvent(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: /admin/dance/events');
            exit;
        }

        $event = $this->adminDance->getEventForEdit($id);
        if (!$event) {
            Session::setFlash('admin_error', 'Dance event not found.');
            header('Location: /admin/dance/events');
            exit;
        }

        $venues = $this->adminDance->listVenues();
        $audio = $this->adminDance->getEventAudio($id);
        $app = $this->adminDance->appSettings();
        $csrf = Csrf::token('admin_dance_event');
        require __DIR__ . '/../Views/Admin/Dance/dance-event-edit.php';
    }

    public function saveEvent(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
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
        $backUrl = $eventId > 0 ? '/admin/dance/events/edit?id=' . $eventId : '/admin/dance/events/new';

        try {
            $newId = $this->adminDance->saveEvent($_POST);
        } catch (ValidationException $e) {
            Session::setFlash('admin_error', $e->getMessage());
            header('Location: ' . $backUrl);
            exit;
        }

        Session::setFlash('admin_success', 'Dance event saved.');
        header('Location: /admin/dance/events/edit?id=' . $newId);
        exit;
    }

    public function deleteEvent(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
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
            $this->adminDance->deleteEvent($id);
            Session::setFlash('admin_success', 'Event deleted.');
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', $e->getMessage());
        }
        header('Location: /admin/dance/events');
        exit;
    }

    // homepage artists

    public function artists(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
        $app = $this->adminDance->appSettings();
        $artists = $this->adminDance->listArtists();
        require __DIR__ . '/../Views/Admin/Dance/dance-artists-list.php';
    }

    public function artistsNew(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
        $app = $this->adminDance->appSettings();
        $csrf = Csrf::token('admin_dance_artist');
        $isNew = true;
        $row = ['name' => '', 'slug' => '', 'bio' => '', 'image' => ''];
        require __DIR__ . '/../Views/Admin/Dance/dance-artist-edit.php';
    }

    public function artistsEdit(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
        $slug = trim((string) ($_GET['slug'] ?? ''));

        $row = $slug !== '' ? $this->adminDance->getArtistForEdit($slug) : null;
        if ($row === null) {
            Session::setFlash('admin_error', 'Artist not found.');
            header('Location: /admin/dance/artists');
            exit;
        }

        $app = $this->adminDance->appSettings();
        $csrf = Csrf::token('admin_dance_artist');
        $isNew = false;
        require __DIR__ . '/../Views/Admin/Dance/dance-artist-edit.php';
    }

    public function saveArtist(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
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
        $backUrl = $originalSlug !== ''
            ? '/admin/dance/artists/edit?slug=' . rawurlencode($originalSlug)
            : '/admin/dance/artists/new';

        try {
            $this->adminDance->saveArtist($_POST);
        } catch (ValidationException $e) {
            Session::setFlash('admin_error', $e->getMessage());
            header('Location: ' . $backUrl);
            exit;
        }

        Session::setFlash('admin_success', 'Artist saved.');
        header('Location: /admin/dance/artists');
        exit;
    }

    public function deleteArtist(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
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

        $slug = trim((string) ($_POST['slug'] ?? ''));
        try {
            $this->adminDance->deleteArtist($slug);
            Session::setFlash('admin_success', 'Artist removed from the Dance page.');
        } catch (ValidationException $e) {
            Session::setFlash('admin_error', $e->getMessage());
        }
        header('Location: /admin/dance/artists');
        exit;
    }

    // cms settings form

    public function index(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $app = $this->adminDance->appSettings();
        require __DIR__ . '/../Views/Admin/Dance/dance-index.php';
    }

    public function showForm(?string $error = null, ?string $success = null): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $vm = $this->adminDance->buildEditViewModel(
            Csrf::token('cms_dance'),
            Csrf::token('cms_upload'),
            $error,
            $success
        );
        require __DIR__ . '/../Views/Admin/Dance/DanceEdit.php';
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

        try {
            $this->adminDance->saveSettings($_POST);
            $this->showForm(null, 'Saved.');
        } catch (ValidationException $e) {
            $this->showForm($e->getMessage(), null);
        }
    }

}
