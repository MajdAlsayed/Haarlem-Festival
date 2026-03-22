<?php

declare(strict_types=1);

namespace App\ViewModels;

/**
 * Admin order export page: column choices + format.
 */
final class AdminOrderExportViewModel
{
    /** @param array<string, string> $columnLabels key => human label */
    public function __construct(
        public string $csrf,
        public array $appSettings,
        public array $columnLabels,
        public ?string $error = null
    ) {
    }
}
