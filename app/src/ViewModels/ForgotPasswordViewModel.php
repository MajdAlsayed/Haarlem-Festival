<?php

declare(strict_types=1);

namespace App\ViewModels;

final class ForgotPasswordViewModel
{
    public function __construct(
        public string $csrf,
        public ?string $error = null,
        public ?string $success = null,
        public ?string $dummyLink = null,
        public string $identifier = '',
    ) {}
}