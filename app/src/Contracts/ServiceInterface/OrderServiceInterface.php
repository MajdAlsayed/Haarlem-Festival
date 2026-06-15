<?php

namespace App\Contracts\ServiceInterface;

interface OrderServiceInterface
{
    /** @return array[] */
    public function ordersForUser(int $userId): array;

    /** @return array{order: array, lines: array[], tickets: array[]}|null */
    public function orderDetailForUser(int $orderId, int $userId): ?array;

    public function invoicePdf(int $orderId, int $userId): ?string;

    public function ticketsPdf(int $orderId, int $userId): ?string;

    public function appSettings(): array;
}
