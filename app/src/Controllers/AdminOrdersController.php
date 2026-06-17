<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Repositories\OrderRepository;
use App\Services\AdminOrdersService;
use App\Services\SettingsService;
use App\ViewModels\AdminOrderDetailViewModel;
use App\ViewModels\AdminOrderTicketsViewModel;
use App\ViewModels\AdminOrdersListViewModel;

// admin order list + detail (read only)
final class AdminOrdersController
{
    private AdminOrdersService $orders;

    public function __construct()
    {
        $this->orders = new AdminOrdersService(
            new OrderRepository(),
            new SettingsService(new SettingsRepository()),
        );
    }

    public function index(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $viewModel = new AdminOrdersListViewModel($this->orders->allOrders(), $this->orders->appSettings());

        require __DIR__ . '/../Views/Admin/OrdersList.php';
    }

    public function show(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $orderId = (int) ($_GET['order_id'] ?? 0);
        $data = $this->orders->orderDetail($orderId);

        $app = $this->orders->appSettings();
        $vm = new AdminOrderDetailViewModel($data['order'], $data['lines'], $data['tickets'], $app);

        require __DIR__ . '/../Views/Admin/OrderDetail.php';
    }

    public function tickets(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $orderId = (int) ($_GET['order_id'] ?? 0);
        $data = $this->orders->orderTickets($orderId);

        $app = $this->orders->appSettings();
        $vm = new AdminOrderTicketsViewModel($data['order'], $data['tickets'], $app);

        require __DIR__ . '/../Views/Admin/OrderTickets.php';
    }
}
