<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Exceptions\ValidationException;
use App\Repositories\PageRepository;
use App\Repositories\SettingsRepository;
use App\Services\AdminHomepageService;
use App\Services\PageService;
use App\Services\SettingsService;

// admin cms for homepage copy (wysiwyg fields)
final class AdminHomepageController
{
    private AdminHomepageService $adminHomepage;

    public function __construct()
    {
        $this->adminHomepage = new AdminHomepageService(
            new PageService(new PageRepository()),
            new SettingsService(new SettingsRepository()),
        );
    }

    public function showForm(?string $error = null, ?string $success = null): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $vm = $this->adminHomepage->buildEditViewModel(
            Csrf::token('cms_homepage'),
            Csrf::token('cms_upload'),
            $error,
            $success
        );
        require __DIR__ . '/../Views/Admin/HomepageEdit.php';
    }

    public function save(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
        if (!Csrf::validate('cms_homepage', $_POST['_csrf'] ?? null)) {
            $this->showForm('Wrong CSRF token.', null);
            return;
        }

        try {
            $this->adminHomepage->saveSettings($_POST);
            $this->showForm(null, 'Saved.');
        } catch (ValidationException $e) {
            $this->showForm($e->getMessage(), null);
        }
    }
}
