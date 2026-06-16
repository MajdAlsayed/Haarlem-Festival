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

        try {
            $search  = trim((string)($_GET['search'] ?? ''));
            $sortBy  = (string)($_GET['sort'] ?? 'created_at');
            $sortDir = (string)($_GET['dir'] ?? 'DESC');

            $users = $this->userService->getAllUsers($search, $sortBy, $sortDir);

            $viewModel = new AdminUserViewModel(
                users: $users,
                search: $search,
                sortBy: $sortBy,
                sortDir: $sortDir,
            );

            require __DIR__ . '/../Views/Admin/users-list.php';
        } catch (\Exception $e) {
            error_log('AdminUserController::index error: ' . $e->getMessage());
            require __DIR__ . '/../Views/error.php';
        }
    }

    public function edit(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        try {
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
            $csrf  = Csrf::token('admin_user');

            require __DIR__ . '/../Views/Admin/users-edit.php';
        } catch (\Exception $e) {
            error_log('AdminUserController::edit error: ' . $e->getMessage());
            require __DIR__ . '/../Views/error.php';
        }
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

        try {
            $id = (int)($_POST['user_id'] ?? 0);

            // Redirect back if required fields are missing
            if ($id <= 0) {
                Session::setFlash('admin_error', 'Required fields missing.');
                header('Location: /admin/users');
                exit;
            }

            // Get existing user and update its fields
            $user = $this->userService->findById($id);
            if (!$user) {
                Session::setFlash('admin_error', 'User not found.');
                header('Location: /admin/users');
                exit;
            }

            $firstName = trim((string)($_POST['first_name'] ?? ''));
            $email     = trim((string)($_POST['email'] ?? ''));

            if ($firstName === '' || $email === '') {
                Session::setFlash('admin_error', 'Required fields missing.');
                header('Location: /admin/users/edit?id=' . $id);
                exit;
            }

            // Update User model fields
            $user->roleId    = (int)($_POST['role_id'] ?? 0);
            $user->firstName = $firstName;
            $user->lastName  = trim((string)($_POST['last_name'] ?? ''));
            $user->email     = $email;
            $user->isActive  = !empty($_POST['is_active']);

            // Pass the whole User object
            $this->userService->updateUser($user);

            Session::setFlash('admin_success', 'User updated.');
            header('Location: /admin/users');
            exit;
        } catch (\Exception $e) {
            error_log('AdminUserController::update error: ' . $e->getMessage());
            Session::setFlash('admin_error', 'An unexpected error occurred.');
            header('Location: /admin/users');
            exit;
        }
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

        try {
            $id = (int)($_POST['user_id'] ?? 0);
            if ($id <= 0) {
                header('Location: /admin/users');
                exit;
            }

            $this->userService->deleteUser($id);
            Session::setFlash('admin_success', 'User deleted.');
            header('Location: /admin/users');
            exit;
        } catch (\Exception $e) {
            error_log('AdminUserController::delete error: ' . $e->getMessage());
            Session::setFlash('admin_error', 'An unexpected error occurred.');
            header('Location: /admin/users');
            exit;
        }
    }
}