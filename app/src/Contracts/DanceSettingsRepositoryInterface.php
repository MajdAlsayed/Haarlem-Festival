<?php

declare(strict_types=1);

namespace App\Contracts;

interface DanceSettingsRepositoryInterface
{

    public function getMergedWithConfig(): array;

    public function upsertSetting(string $key, string $value): bool;
}
