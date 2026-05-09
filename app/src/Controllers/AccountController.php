<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\OrderRepository;
use App\Repositories\SettingsRepository;

/** Customer order history, invoice and tickets; pending pay-later orders expose payment actions here. */
final class AccountController
{
    public function orders(): void
    {
        $userId = $this->requireCustomerLogin();
        $list = (new OrderRepository())->listOrdersForUser($userId);
        $app = (new SettingsRepository())->getAll();

        require __DIR__ . '/../Views/Account/orders.php';
    }

    public function orderDetail(int $orderId): void
    {
        $userId = $this->requireCustomerLogin();
        $orders = new OrderRepository();
        $order = $orders->findForCustomer($orderId, $userId);
        if ($order === null) {
            http_response_code(404);
            echo 'Order not found.';
            exit;
        }

        $lines = $orders->getOrderLineItemsForInvoice($orderId);
        $tickets = $orders->getTicketCodesForOrder($orderId);
        $app = (new SettingsRepository())->getAll();
        $orderError = Session::getFlash('order_error');
        // Same CSRF bucket as /checkout so the pending-order forms can post to /checkout/pay-pending*.
        $checkoutCsrf = Csrf::token('checkout');
        $stripeOn = \App\Services\StripePaymentService::isConfigured();

        require __DIR__ . '/../Views/Account/order.php';
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
