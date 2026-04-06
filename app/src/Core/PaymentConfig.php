<?php

declare(strict_types=1);

namespace App\Core;

/** Public site base URL for Stripe redirect URLs (app config / APP_PUBLIC_URL). */
final class PaymentConfig
{
    public static function publicBaseUrl(): string
    {
        $app = require dirname(__DIR__) . '/Config/app.php';

        return $app['public_base_url'] ?? 'http://localhost';
    }
}
