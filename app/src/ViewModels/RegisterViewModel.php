<?php

declare(strict_types=1);

namespace App\ViewModels;

class RegisterViewModel
{
    public function __construct(
        public string  $csrf,
        public string  $captchaQuestion,
        public ?string $error           = null,
        public string  $username        = '',
        public string  $email           = '',
        public string  $firstName       = '',
        public string  $lastName        = '',
    ) {}
}