<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\OrderRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;
use App\Services\InvoicePdfService;
use App\Services\OrderService;
use App\Services\StripePaymentService;
use App\Services\TicketPdfService;

/** Customer order history, invoice and tickets; pending pay-later orders expose payment actions here. */
final class AccountController
{
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService(
            new OrderRepository(),
            new UserRepository(),
            new SettingsRepository(),
            new InvoicePdfService(),
            new TicketPdfService()
        );
    }

    public function orders(): void
    {
        $userId = $this->requireCustomerLogin();
        $list = $this->orderService->ordersForUser($userId);
        $app = $this->orderService->appSettings();

        require __DIR__ . '/../Views/Account/orders.php';
    }

    public function orderDetail(int $orderId): void
    {
        $userId = $this->requireCustomerLogin();
        $data = $this->orderService->orderDetailForUser($orderId, $userId);
        if ($data === null) {
            http_response_code(404);
            echo 'Order not found.';
            exit;
        }

        $order = $data['order'];
        $lines = $data['lines'];
        $tickets = $data['tickets'];
        $app = $this->orderService->appSettings();
        $orderError = Session::getFlash('order_error');
        // same CSRF bucket as /checkout so the pending-order forms can post to /checkout/pay-pending*
        $checkoutCsrf = Csrf::token('checkout');
        $stripeOn = StripePaymentService::isConfigured();

        require __DIR__ . '/../Views/Account/order.php';
    }

    public function downloadInvoice(int $orderId): void
    {
        $userId = $this->requireCustomerLogin();
        $pdf = $this->orderService->invoicePdf($orderId, $userId);
        if ($pdf === null) {
            http_response_code(404);
            echo 'Invoice not available.';
            exit;
        }

        $this->streamPdf($pdf, 'invoice-' . $orderId . '.pdf');
    }

    public function downloadTickets(int $orderId): void
    {
        $userId = $this->requireCustomerLogin();
        $pdf = $this->orderService->ticketsPdf($orderId, $userId);
        if ($pdf === null) {
            http_response_code(404);
            echo 'Tickets not available.';
            exit;
        }

        $this->streamPdf($pdf, 'tickets-' . $orderId . '.pdf');
    }

    // send the pdf straight to the browser so it opens in a new tab
    private function streamPdf(string $pdf, string $filename): void
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    private function requireCustomerLogin(): int
    {
        $uid = isset($_SESSION['auth']['user_id']) ? (int) $_SESSION['auth']['user_id'] : 0;
        if ($uid <= 0) {
            header('Location: /login?return=/account/orders');
            exit;
        }

        return $uid;
    }
}
