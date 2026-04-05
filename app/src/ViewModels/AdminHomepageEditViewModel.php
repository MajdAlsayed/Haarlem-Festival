<?php

declare(strict_types=1);

namespace App\ViewModels;

final class AdminHomepageEditViewModel
{
    public function __construct(
        public string $csrf,
        public string $pageTitle,
        public array $cmsHome,
        public string $uploadCsrf,
        public array $appSettings,
        public ?string $error = null,
        public ?string $success = null
    ) {
    }
}
