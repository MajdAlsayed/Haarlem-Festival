<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Exceptions\ValidationException;
use App\Repositories\OrderRepository;
use App\Services\OrderExportService;
use App\Services\SettingsService;
use App\ViewModels\AdminOrderExportViewModel;

// csv/excel download for orders
final class AdminOrderExportController
{
    private OrderExportService $exportService;

    public function __construct()
    {
        $this->exportService = new OrderExportService(
            new OrderRepository(),
            new SettingsService(new SettingsRepository()),
        );
    }

    public function handle(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->exportPost();
            return;
        }

        $this->showForm(null);
    }

    private function showForm(?string $error): void
    {
        $viewModel = AdminOrderExportViewModel::fromPageData(
            $this->exportService->getExportPageData(),
            Csrf::token('admin_order_export'),
            $error,
        );
        require __DIR__ . '/../Views/Admin/OrderExport.php';
    }

    private function exportPost(): void
    {
        if (!Csrf::validate('admin_order_export', $_POST['_csrf'] ?? null)) {
            $this->showForm('Invalid security token. Please try again.');
            return;
        }

        try {
            $download = $this->exportService->buildExport($_POST);
        } catch (ValidationException $e) {
            $this->showForm($e->getMessage());
            return;
        }

        $this->sendDownload($download['body'], $download['mime'], $download['filename']);
    }

    private function sendDownload(string $body, string $mime, string $filename): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        echo $body;
        exit;
    }
}
