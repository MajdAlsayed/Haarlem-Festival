<?php

declare(strict_types=1);

namespace App\ViewModels;

final class AdminOrdersListViewModel
{
    /** @param list<array<string, mixed>> $orders */
    public function __construct(
        public array $orders,
        public array $appSettings
    ) {
    }

    public function totalCount(): int
    {
        return count($this->orders);
    }

    public function countByStatus(string $status): int
    {
        $n = 0;
        foreach ($this->orders as $order) {
            if (($order['status'] ?? '') === $status) {
                $n++;
            }
        }

        return $n;
    }

    public function revenueTotal(): float
    {
        $sum = 0.0;
        foreach ($this->orders as $order) {
            if (($order['status'] ?? '') === 'paid') {
                $sum += (float) ($order['total_amount'] ?? 0);
            }
        }

        return $sum;
    }

    public function formatMoney(mixed $amount): string
    {
        $n = is_numeric($amount) ? (float) $amount : 0.0;

        return '€ ' . number_format($n, 2, ',', '.');
    }

    public function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'paid' => 'admin-badge admin-badge-paid',
            'pending' => 'admin-badge admin-badge-pending',
            'canceled', 'cancelled' => 'admin-badge admin-badge-canceled',
            default => 'admin-badge admin-badge-inactive',
        };
    }
}
