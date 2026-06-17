<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

interface OrderExportServiceInterface
{

    public function appSettings(): array;

    public function getExportPageData(): array;

    public function columnLabels(): array;

    public function buildExport(array $post): array;
}
