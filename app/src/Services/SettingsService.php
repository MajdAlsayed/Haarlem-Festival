<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\SettingsServiceInterface;
use App\Contracts\SettingsRepositoryInterface;

// site settings — thin wrapper on SettingsRepository
final class SettingsService implements SettingsServiceInterface
{
    public function __construct(
        private SettingsRepositoryInterface $settingsRepository,
    ) {
    }

    public function getAll(): array
    {
        return $this->settingsRepository->getAll();
    }

    public function getMergedCmsHome(): array
    {
        return $this->settingsRepository->getMergedCmsHome();
    }

    public function upsertSetting(string $key, string $value): bool
    {
        return $this->settingsRepository->upsertSetting($key, $value);
    }
}
