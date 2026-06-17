<?php

namespace App\Contracts\ServiceInterface;

interface AdminOrdersServiceInterface
{

    public function appSettings(): array;

    public function allOrders(): array;

    public function orderDetail(int $orderId): array;

    public function orderTickets(int $orderId): array;
}
