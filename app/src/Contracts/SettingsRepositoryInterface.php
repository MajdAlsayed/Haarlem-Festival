<?php

declare(strict_types=1);

namespace App\Contracts;

interface SettingsRepositoryInterface
{

    public function getAll(): array;

    public function getMergedCmsHome(): array;

    public function upsertSetting(string $key, string $value): bool;
}
