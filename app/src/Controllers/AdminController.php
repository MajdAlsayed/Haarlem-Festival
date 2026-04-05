<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\PageRepository;
use App\Repositories\SettingsRepository;

/**
 * CMS admin: requires logged-in user with admin role (role_id = 1).
 */
final class AdminController
{
    private const ADMIN_ROLE_ID = 1;

    private PageRepository $pageRepository;
    private SettingsRepository $settingsRepository;

    public function __construct()
    {
        $this->pageRepository = new PageRepository();
        $this->settingsRepository = new SettingsRepository();
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

    private function appSettings(): array
    {
        return $this->settingsRepository->getAll();
    }

    /** GET /admin – dashboard */
    public function index(): void
    {
        $this->requireAdmin();
        $app = $this->appSettings();
        $pages = $this->pageRepository->getAllForAdmin();
        require __DIR__ . '/../Views/Admin/dashboard.php';
    }

    /** GET /admin/pages – list pages */
    public function pages(): void
    {
        $this->requireAdmin();
        $app = $this->appSettings();
        $pages = $this->pageRepository->getAllForAdmin();
        require __DIR__ . '/../Views/Admin/pages-list.php';
    }

    /** GET /admin/pages/edit?id= – edit form */
    public function editPage(): void
    {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: /admin/pages');
            exit;
        }
        $page = $this->pageRepository->getById($id);
        if (!$page) {
            header('Location: /admin/pages');
            exit;
        }
        $app = $this->appSettings();
        $csrf = Csrf::token('admin_page');
        require __DIR__ . '/../Views/Admin/page-edit.php';
    }

    /** POST /admin/pages/update – update page */
    public function updatePage(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/pages');
            exit;
        }
        if (!Csrf::validate('admin_page', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request. Please try again.');
            header('Location: /admin/pages');
            exit;
        }
        $id = isset($_POST['page_id']) ? (int) $_POST['page_id'] : 0;
        if ($id <= 0) {
            header('Location: /admin/pages');
            exit;
        }
        $page = $this->pageRepository->getById($id);
        if (!$page) {
            header('Location: /admin/pages');
            exit;
        }
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $slug = preg_replace('/[^a-z0-9\-]/', '-', strtolower($slug));
        $slug = trim(preg_replace('/-+/', '-', $slug), '-') ?: $page['slug'];
        $isPublished = !empty($_POST['is_published']);
        if ($title === '') {
            Session::setFlash('admin_error', 'Title is required.');
            header('Location: /admin/pages/edit?id=' . $id);
            exit;
        }
        $this->pageRepository->update($id, $title, $slug ?: $page['slug'], $isPublished);
        Session::setFlash('admin_success', 'Page updated.');
        header('Location: /admin/pages');
        exit;
    }
}
