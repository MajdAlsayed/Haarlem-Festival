<?php

declare(strict_types=1);

namespace App\ViewModels;

class LoginViewModel
{
    public function __construct(
        public string  $csrf,
        public ?string $error = null,
    ) {}
}