<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\OrderServiceInterface;
use App\Contracts\ServiceInterface\SettingsServiceInterface;
use App\Contracts\ServiceInterface\UserServiceInterface;
use App\Repositories\OrderRepository;
use App\Views\InvoiceView;
use App\Views\TicketView;

// account orders — list, detail, invoice + ticket pdf downloads
class OrderService implements OrderServiceInterface
{
    private const DEFAULT_SITE_NAME = 'Haarlem Festival';

    public function __construct(
        private OrderRepository $orderRepository,
        private UserServiceInterface $userService,
        private SettingsServiceInterface $settingsService,
        private InvoicePdfService $invoicePdfService,
        private TicketPdfService $ticketPdfService,
        private QrCodeService $qrCodeService = new QrCodeService(),
    ) {
    }

    // paid + pending rows for /account/orders
    public function ordersForUser(int $userId): array
    {
        return $this->orderRepository->listOrdersForUser($userId);
    }

    // must match logged-in user
    public function orderDetailForUser(int $orderId, int $userId): ?array
    {
        $order = $this->orderRepository->findForCustomer($orderId, $userId);
        if ($order === null) {
            return null;
        }

        return $this->buildOrderDetail($orderId, $order);
    }

    // only if customer owns it and order is paid
    public function invoicePdf(int $orderId, int $userId): ?string
    {
        $order = $this->paidOrderForUser($orderId, $userId);
        if ($order === null) {
            return null;
        }

        [$name, $email] = $this->customerContact($userId);
        $lines = $this->orderRepository->getOrderLineItemsForInvoice($orderId);
        $html = InvoiceView::html($order, $lines, $name, $email, $this->siteName());

        return $this->invoicePdfService->render($html);
    }

    // qr pdfs via TicketView + QrCodeService
    public function ticketsPdf(int $orderId, int $userId): ?string
    {
        $order = $this->paidOrderForUser($orderId, $userId);
        if ($order === null) {
            return null;
        }

        [$name] = $this->customerContact($userId);
        $tickets = $this->orderRepository->getTicketsWithDetailsForOrder($orderId);
        $qrByCode = $this->qrCodeService->pngDataUrisForCodes($this->ticketCodes($tickets));
        $html = TicketView::html($tickets, $name, $this->siteName(), $qrByCode);

        return $this->ticketPdfService->render($html);
    }

    // site name etc for account layout
    public function appSettings(): array
    {
        return $this->settingsService->getAll();
    }

    private function buildOrderDetail(int $orderId, array $order): array
    {
        return [
            'order' => $order,
            'lines' => $this->orderRepository->getOrderLineItemsForInvoice($orderId),
            'tickets' => $this->orderRepository->getTicketCodesForOrder($orderId),
        ];
    }

    private function paidOrderForUser(int $orderId, int $userId): ?array
    {
        $order = $this->orderRepository->findForCustomer($orderId, $userId);
        if ($order === null || $this->rowText($order, 'status') !== 'paid') {
            return null;
        }

        return $order;
    }

    private function customerContact(int $userId): array
    {
        $user = $this->userService->findById($userId);
        if ($user === null) {
            return ['', ''];
        }

        return [trim($user->firstName . ' ' . $user->lastName), $user->email];
    }

    private function siteName(): string
    {
        $name = $this->settingText($this->appSettings(), 'site_name');

        return $name !== '' ? $name : self::DEFAULT_SITE_NAME;
    }

    private function settingText(array $settings, string $key): string
    {
        if (isset($settings[$key]) && is_string($settings[$key])) {
            return $settings[$key];
        }

        return '';
    }

    private function rowText(array $row, string $key): string
    {
        return isset($row[$key]) ? (string) $row[$key] : '';
    }

    private function ticketCodes(array $tickets): array
    {
        $codes = [];
        foreach ($tickets as $ticket) {
            $codes[] = $this->rowText($ticket, 'ticket_code');
        }

        return $codes;
    }
}
