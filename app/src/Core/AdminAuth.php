<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\UserRepository;

final class AdminAuth
{
    // Same check as requireAdmin but no 403 message (used for JSON).
    public static function isAdmin(): bool
    {
        $auth = $_SESSION['auth'] ?? null;
        if (!is_array($auth)) {
            return false;
        }

        $repo = new UserRepository();
        $adminRoleId = $repo->getRoleIdByName('admin');

        return $adminRoleId !== null && (int) ($auth['role_id'] ?? 0) === $adminRoleId;
    }

    public static function requireAdmin(): bool
    {
        $auth = $_SESSION['auth'] ?? null;
        if (!is_array($auth)) {
            http_response_code(403);
            echo 'Access denied. Please log in as an administrator.';
            return false;
        }

        $repo = new UserRepository();
        $adminRoleId = $repo->getRoleIdByName('admin');
        if ($adminRoleId === null || (int) ($auth['role_id'] ?? 0) !== $adminRoleId) {
            http_response_code(403);
            echo 'Access denied. Administrators only.';
            return false;
        }

        return true;
    }
}
