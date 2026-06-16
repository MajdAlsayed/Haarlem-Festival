<?php

namespace App\Contracts\ServiceInterface;

interface OrderExportServiceInterface
{
    /** @return array<string, string> */
    public function columnLabels(): array;

    /** @return array<string, mixed> */
    public function appSettings(): array;

    /**
     * @param array<string, mixed> $post
     * @return array{body: string, mime: string, filename: string}
     */
    public function buildExport(array $post): array;
}
