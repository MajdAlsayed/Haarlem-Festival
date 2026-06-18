<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

interface SettingsServiceInterface
{
    public function getAll(): array;

    public function getMergedCmsHome(): array;

    public function upsertSetting(string $key, string $value): bool;
}
