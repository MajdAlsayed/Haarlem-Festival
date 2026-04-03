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
}
