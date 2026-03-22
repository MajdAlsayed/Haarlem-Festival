<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Repositories\OrderRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;
use App\Services\OrderExportService;
use App\ViewModels\AdminOrderExportViewModel;

/**
 * Administrator: export orders as CSV or Excel (HTML table Excel opens).
 * Acceptance: selectable columns; total_amount and paid_at included in options.
 */
final class AdminOrderExportController
{
    /** Whitelist: DB/export row keys => column title */
    public const COLUMN_LABELS = [
        'order_id' => 'Order ID',
        'user_id' => 'User ID',
        'customer_email' => 'Customer email',
        'customer_first_name' => 'First name',
        'customer_last_name' => 'Last name',
        'status' => 'Status',
        'total_amount' => 'Total amount',
        'paid_at' => 'Paid at',
        'created_at' => 'Created at',
        'line_items_count' => 'Line items',
    ];

    private OrderRepository $orderRepository;
    private OrderExportService $exportService;
    private SettingsRepository $settingsRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->exportService = new OrderExportService();
        $this->settingsRepository = new SettingsRepository();
        $this->userRepository = new UserRepository();
    }

    public function handle(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'POST') {
            $this->exportPost();
            return;
        }

        $this->showForm(null);
    }

    private function showForm(?string $error): void
    {
        $viewModel = new AdminOrderExportViewModel(
            csrf: Csrf::token('admin_order_export'),
            appSettings: $this->settingsRepository->getAll(),
            columnLabels: self::COLUMN_LABELS,
            error: $error
        );
        require __DIR__ . '/../Views/Admin/OrderExport.php';
    }

    private function exportPost(): void
    {
        if (!Csrf::validate('admin_order_export', $_POST['_csrf'] ?? null)) {
            $this->showForm('Invalid security token. Please try again.');
            return;
        }

        $format = strtolower(trim((string) ($_POST['format'] ?? 'csv')));
        if (!in_array($format, ['csv', 'excel'], true)) {
            $this->showForm('Choose a valid export format.');
            return;
        }

        $selected = $_POST['columns'] ?? null;
        if (!is_array($selected)) {
            $selected = [];
        }

        $allowed = array_keys(self::COLUMN_LABELS);
        $columnKeys = [];
        foreach ($selected as $key) {
            if (!is_string($key)) {
                continue;
            }
            $key = trim($key);
            if (in_array($key, $allowed, true)) {
                $columnKeys[] = $key;
            }
        }

        if ($columnKeys === []) {
            $columnKeys = $allowed;
        }

        $rows = $this->orderRepository->getAllForExport();

        if ($format === 'csv') {
            $body = $this->exportService->toCsvString($rows, $columnKeys, self::COLUMN_LABELS);
            $this->sendDownload(
                $body,
                'text/csv; charset=UTF-8',
                'haarlem-orders-' . gmdate('Y-m-d') . '.csv'
            );
            return;
        }

        $body = $this->exportService->toExcelHtmlTable($rows, $columnKeys, self::COLUMN_LABELS);
        $this->sendDownload(
            $body,
            'application/vnd.ms-excel; charset=UTF-8',
            'haarlem-orders-' . gmdate('Y-m-d') . '.xls'
        );
    }

    /**
     * @param non-empty-string $mime
     */
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

    private function requireAdmin(): bool
    {
        $auth = $_SESSION['auth'] ?? null;
        if (!is_array($auth)) {
            http_response_code(403);
            echo 'Access denied. Please log in as an administrator.';
            return false;
        }

        $adminRoleId = $this->userRepository->getRoleIdByName('admin');
        if ($adminRoleId === null || (int) ($auth['role_id'] ?? 0) !== $adminRoleId) {
            http_response_code(403);
            echo 'Access denied. Administrators only.';
            return false;
        }

        return true;
    }
}
