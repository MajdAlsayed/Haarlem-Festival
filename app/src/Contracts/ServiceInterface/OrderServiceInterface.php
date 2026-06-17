<?php

namespace App\Contracts\ServiceInterface;

interface OrderServiceInterface
{

    public function ordersForUser(int $userId): array;

    public function orderDetailForUser(int $orderId, int $userId): ?array;

    public function invoicePdf(int $orderId, int $userId): ?string;

    public function ticketsPdf(int $orderId, int $userId): ?string;

    public function appSettings(): array;
}
