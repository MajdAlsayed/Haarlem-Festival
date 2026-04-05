<?php

declare(strict_types=1);

namespace App\ViewModels;

/**
 * Admin: list orders (read-only table).
 */
final class AdminOrdersListViewModel
{
    /** @param list<array<string, mixed>> $orders */
    public function __construct(
        public array $orders,
        public array $appSettings
    ) {
    }
}
