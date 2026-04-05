<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Repositories\OrderRepository;
use App\Repositories\SettingsRepository;
use App\ViewModels\AdminOrdersListViewModel;

/**
 * CMS: administrators view all orders in a table (read-only).
 */
final class AdminOrdersController
{
    public function index(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $orderRepository = new OrderRepository();
        $settingsRepository = new SettingsRepository();

        $orders = $orderRepository->getAllForExport();
        $viewModel = new AdminOrdersListViewModel($orders, $settingsRepository->getAll());

        require __DIR__ . '/../Views/Admin/OrdersList.php';
    }

    /** GET /admin/orders/tickets?order_id= — ticket codes for an order (admin). */
    public function tickets(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $orderId = (int) ($_GET['order_id'] ?? 0);
        if ($orderId <= 0) {
            header('Location: /admin/orders');
            exit;
        }

        $orderRepository = new OrderRepository();
        $order = $orderRepository->findOrderById($orderId);
        if ($order === null) {
            http_response_code(404);
            echo 'Order not found.';
            exit;
        }

        $ticketRows = $orderRepository->getTicketCodesForOrder($orderId);
        $app = (new SettingsRepository())->getAll();

        require __DIR__ . '/../Views/Admin/OrderTickets.php';
    }
}
