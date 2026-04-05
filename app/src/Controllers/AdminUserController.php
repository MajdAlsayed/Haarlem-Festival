<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Core\Session;
use App\Contracts\ServiceInterface\UserServiceInterface;
use App\Repositories\UserRepository;
use App\Services\UserService;
use App\ViewModels\AdminUserViewModel;

final class AdminUserController
{
    private UserServiceInterface $userService;

    public function __construct()
    {
        $this->userService = new UserService(new UserRepository());
    }

    public function index(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $search = trim((string)($_GET['search'] ?? ''));
        $sortBy = (string)($_GET['sort'] ?? 'created_at');
        $sortDir = (string)($_GET['dir'] ?? 'DESC');

        $users = $this->userService->getAllUsers($search, $sortBy, $sortDir);

        $viewModel = new AdminUserViewModel(
            users: $users,
            search: $search,
            sortBy: $sortBy,
            sortDir: $sortDir,
        );

        require __DIR__ . '/../Views/Admin/users-list.php';
    }

    public function edit(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: /admin/users');
            exit;
        }

        $user = $this->userService->findById($id);
        if (!$user) {
            header('Location: /admin/users');
            exit;
        }

        $roles = $this->userService->getAllRoles();
        $csrf = Csrf::token('admin_user');

        require __DIR__ . '/../Views/Admin/users-edit.php';
    }

    public function update(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (!Csrf::validate('admin_user', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request. Please try again.');
            header('Location: /admin/users');
            exit;
        }

        $id = (int)($_POST['user_id'] ?? 0);
        $roleId = (int)($_POST['role_id'] ?? 0);
        $firstName = trim((string)($_POST['first_name'] ?? ''));
        $lastName = trim((string)($_POST['last_name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $isActive = !empty($_POST['is_active']);

        if ($id <= 0 || $firstName === '' || $email === '') {
            Session::setFlash('admin_error', 'Required fields missing.');
            header('Location: /admin/users/edit?id=' . $id);
            exit;
        }

        $this->userService->updateUser($id, $roleId, $firstName, $lastName, $email, $isActive);
        Session::setFlash('admin_success', 'User updated.');
        header('Location: /admin/users');
        exit;
    }

    public function delete(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (!Csrf::validate('admin_user', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request. Please try again.');
            header('Location: /admin/users');
            exit;
        }

        $id = (int)($_POST['user_id'] ?? 0);
        if ($id <= 0) {
            header('Location: /admin/users');
            exit;
        }

        $this->userService->deleteUser($id);
        Session::setFlash('admin_success', 'User deleted.');
        header('Location: /admin/users');
        exit;
    }
}