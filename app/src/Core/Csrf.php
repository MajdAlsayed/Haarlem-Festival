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

    // Read token without making a new one (cart footer).
    public static function peek(string $form): ?string
    {
        Session::start();
        $t = $_SESSION['_csrf'][$form] ?? null;

        return is_string($t) ? $t : null;
    }

    public static function validate(string $form, ?string $token): bool
    {
        Session::start();

        $expected = $_SESSION['_csrf'][$form] ?? null;

        if (!is_string($expected) || !is_string($token)) {
            return false;
        }

        if (!hash_equals($expected, $token)) {
            return false;
        }

        unset($_SESSION['_csrf'][$form]);

        return true;
    }

    // Like validate() but keeps the token (multiple uploads on same page).
    public static function validateWithoutConsuming(string $form, ?string $token): bool
    {
        Session::start();

        $expected = $_SESSION['_csrf'][$form] ?? null;

        if (!is_string($expected) || !is_string($token)) {
            return false;
        }

        return hash_equals($expected, $token);
    }
}