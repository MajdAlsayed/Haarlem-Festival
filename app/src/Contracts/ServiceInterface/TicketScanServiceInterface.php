<?php

namespace App\Contracts\ServiceInterface;

interface TicketScanServiceInterface
{
    public function appSettings(): array;

    /** @return array<string, mixed> */
    public function scanInput(array $post): array;
}
