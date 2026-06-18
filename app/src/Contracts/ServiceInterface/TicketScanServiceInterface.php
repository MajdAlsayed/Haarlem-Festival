<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

interface TicketScanServiceInterface
{
    public function appSettings(): array;

    public function scanInput(array $post): array;
}
