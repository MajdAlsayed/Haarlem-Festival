<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\UserRepository;

final class TicketScannerAuth
{

    private static ?array $roleIds = null;

    private static function roleIds(UserRepository $repo): array
    {
        // Role ids don’t change at runtime; hitting the DB on every menu render would be overkill.
        if (self::$roleIds === null) {
            self::$roleIds = [
                'admin' => $repo->getRoleIdByName('admin'),
                'employee' => $repo->getRoleIdByName('employee'),
            ];
        }

        return self::$roleIds;
    }

    public static function currentUserCanScan(?UserRepository $repo = null): bool
    {
        $auth = $_SESSION['auth'] ?? null;
        if (!is_array($auth) || empty($auth['user_id'])) {
            return false;
        }

        $repo = $repo ?? new UserRepository();
        $rid = (int) ($auth['role_id'] ?? 0);
        $ids = self::roleIds($repo);

        return ($ids['admin'] !== null && $rid === $ids['admin'])
            || ($ids['employee'] !== null && $rid === $ids['employee']);
    }

    public static function requireScannerAccess(): void
    {
        $auth = $_SESSION['auth'] ?? null;
        if (!is_array($auth) || empty($auth['user_id'])) {
            Session::setFlash('login_error', 'Please log in to use the ticket scanner.');
            header('Location: /login?return=/admin/scan');
            exit;
        }

        if (!self::currentUserCanScan()) {
            Session::setFlash('login_error', 'You do not have permission to use the ticket scanner.');
            header('Location: /');
            exit;
        }
    }
}
