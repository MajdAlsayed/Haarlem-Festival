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

    private SettingsRepository $settingsRepository;

    public function __construct()
    {
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
        require __DIR__ . '/../Views/Admin/dashboard.php';
    }
}
