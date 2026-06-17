<?php

declare(strict_types=1);

namespace App\ViewModels;

final class AdminOrderExportViewModel
{

    public function __construct(
        public string $csrf,
        public array $appSettings,
        public array $columnLabels,
        public ?string $error = null
    ) {
    }

    public static function fromPageData(array $page, string $csrf, ?string $error = null): self
    {
        return new self(
            csrf: $csrf,
            appSettings: $page['app'],
            columnLabels: $page['columns'],
            error: $error,
        );
    }
}
