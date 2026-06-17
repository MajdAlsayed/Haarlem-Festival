<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\AdminOrdersServiceInterface;
use App\Contracts\ServiceInterface\SettingsServiceInterface;
use App\Exceptions\NotFoundException;
use App\Repositories\OrderRepository;

// admin order list + detail pages (read-only)
final class AdminOrdersService implements AdminOrdersServiceInterface
{
    public function __construct(
        private OrderRepository $orderRepository,
        private SettingsServiceInterface $settingsService,
    ) {
    }

    // header/nav settings for admin layout
    public function appSettings(): array
    {
        return $this->settingsService->getAll();
    }

    // same rows as export, for the list page
    public function allOrders(): array
    {
        return $this->orderRepository->getAllForExport();
    }

    // order + line items + ticket codes
    public function orderDetail(int $orderId): array
    {
        $order = $this->orderRepository->findOrderDetailForAdmin($orderId);
        if ($order === null) {
            throw new NotFoundException('Order not found');
        }

        return [
            'order' => $order,
            'lines' => $this->orderRepository->getOrderLineItemsForInvoice($orderId),
            'tickets' => $this->orderRepository->getTicketCodesForOrder($orderId),
        ];
    }

    // order header + qr codes only (tickets sub-page)
    public function orderTickets(int $orderId): array
    {
        $order = $this->orderRepository->findOrderById($orderId);
        if ($order === null) {
            throw new NotFoundException('Order not found');
        }

        return [
            'order' => $order,
            'tickets' => $this->orderRepository->getTicketCodesForOrder($orderId),
        ];
    }
}
