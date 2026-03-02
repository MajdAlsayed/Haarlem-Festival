<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(string $form): string
    {
        Session::start();

        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = [];
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf'][$form] = $token;

        return $token;
    }

    public static function validate(string $form, ?string $token): bool
    {
        Session::start();

        $expected = $_SESSION['_csrf'][$form] ?? null;
        unset($_SESSION['_csrf'][$form]);

        if (!is_string($expected) || !is_string($token)) {
            return false;
        }

        return hash_equals($expected, $token);
    }
}