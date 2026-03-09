<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_start();

        if (!isset($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = true;
        }
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function setFlash(string $key, string $message): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $message;
    }

    public static function getFlash(string $key): ?string
    {
        self::start();
        $msg = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }
}