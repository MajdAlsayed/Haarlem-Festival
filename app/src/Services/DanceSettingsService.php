<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DanceSettingsRepositoryInterface;
use App\Contracts\ServiceInterface\DanceSettingsServiceInterface;

// dance cms settings — thin wrapper on DanceSettingsRepository
final class DanceSettingsService implements DanceSettingsServiceInterface
{
    public function __construct(
        private DanceSettingsRepositoryInterface $danceSettingsRepository,
    ) {
    }

    public function getMergedWithConfig(): array
    {
        return $this->danceSettingsRepository->getMergedWithConfig();
    }

    public function upsertSetting(string $key, string $value): bool
    {
        return $this->danceSettingsRepository->upsertSetting($key, $value);
    }
}
