<?php

declare(strict_types=1);

namespace App\ViewModels;

final class ResetPasswordViewModel
{
    public function __construct(
        public string $csrf,
        public string $token,
        public ?string $error = null,
        public ?string $success = null,
        public array $appSettings = [],
    ) {}
}