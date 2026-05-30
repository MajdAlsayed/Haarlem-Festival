<?php
declare(strict_types=1);

namespace App\ViewModels;

/** Login form (/login): CSRF token, optional flash error, site name/CSS from settings, and ?return= target after success. */
final class LoginViewModel
{
    public function __construct(
        public string $csrf,
        public ?string $error = null,
        public ?string $returnTo = null,
    ) {
    }
}