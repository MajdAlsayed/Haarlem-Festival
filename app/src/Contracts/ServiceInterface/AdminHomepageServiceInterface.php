<?php

namespace App\Contracts\ServiceInterface;

use App\ViewModels\AdminHomepageEditViewModel;

interface AdminHomepageServiceInterface
{
    public function buildEditViewModel(string $csrf, string $uploadCsrf, ?string $error, ?string $success): AdminHomepageEditViewModel;

    public function saveSettings(array $post): void;
}
