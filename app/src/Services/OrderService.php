<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\OrderServiceInterface;
use App\Repositories\OrderRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;
use App\Views\InvoiceView;

// customer-facing order data: history, one order's details, and the invoice/ticket pdfs
class OrderService implements OrderServiceInterface
{
    public function __construct(
        private OrderRepository $orderRepository,
        private UserRepository $userRepository,
        private SettingsRepository $settingsRepository,
        private InvoicePdfService $invoicePdfService,
        private TicketPdfService $ticketPdfService
    ) {
    }

    // every order belonging to this customer
    /** @return array[] */
    public function ordersForUser(int $userId): array
    {
        return $this->orderRepository->listOrdersForUser($userId);
    }

    // one order with its lines + ticket codes, or null if it isn't this customer's
    /** @return array{order: array, lines: array[], tickets: array[]}|null */
    public function orderDetailForUser(int $orderId, int $userId): ?array
    {
        $order = $this->orderRepository->findForCustomer($orderId, $userId);
        if ($order === null) {
            return null;
        }

        return [
            'order' => $order,
            'lines' => $this->orderRepository->getOrderLineItemsForInvoice($orderId),
            'tickets' => $this->orderRepository->getTicketCodesForOrder($orderId),
        ];
    }

    // invoice pdf for a paid order the customer owns, or null when not allowed
    public function invoicePdf(int $orderId, int $userId): ?string
    {
        $order = $this->paidOrderForUser($orderId, $userId);
        if ($order === null) {
            return null;
        }

        [$name, $email] = $this->customer($userId);
        $lines = $this->orderRepository->getOrderLineItemsForInvoice($orderId);

        $html = InvoiceView::html($order, $lines, $name, $email, $this->siteName());

        return $this->invoicePdfService->render($html);
    }

    // tickets pdf (with qr) for a paid order the customer owns, or null when not allowed
    public function ticketsPdf(int $orderId, int $userId): ?string
    {
        $order = $this->paidOrderForUser($orderId, $userId);
        if ($order === null) {
            return null;
        }

        [$name] = $this->customer($userId);
        $tickets = $this->orderRepository->getTicketsWithDetailsForOrder($orderId);

        return $this->ticketPdfService->render($tickets, $name, $this->siteName());
    }

    public function appSettings(): array
    {
        return $this->settingsRepository->getAll();
    }

    // the order only if it belongs to this customer and is paid
    private function paidOrderForUser(int $orderId, int $userId): ?array
    {
        $order = $this->orderRepository->findForCustomer($orderId, $userId);
        if ($order === null || ($order['status'] ?? '') !== 'paid') {
            return null;
        }

        return $order;
    }

    // the customer's display name and email; returns [name, email]
    private function customer(int $userId): array
    {
        $user = $this->userRepository->findById($userId);
        if ($user === null) {
            return ['', ''];
        }

        return [trim($user->firstName . ' ' . $user->lastName), $user->email];
    }

    private function siteName(): string
    {
        return (string) ($this->appSettings()['site_name'] ?? 'Haarlem Festival');
    }
}
