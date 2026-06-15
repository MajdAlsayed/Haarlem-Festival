<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\TicketScannerAuth;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketRepository;
use App\Services\TicketScanService;

/** Door ticket scanner (/admin/scan): single or batch codes, QR in the view; POST then redirect with flash JSON. */
final class TicketScanController
{
    private TicketScanService $ticketScanService;

    public function __construct()
    {
        $this->ticketScanService = new TicketScanService(
            new TicketRepository(),
            new SettingsRepository()
        );
    }

    public function index(): void
    {
        TicketScannerAuth::requireScannerAccess();

        $app = $this->ticketScanService->appSettings();
        $csrf = Csrf::token('scan');
        $error = Session::getFlash('scan_error');
        // the last scan outcome is stored as json in flash so a refresh doesn't re-submit the form
        $result = $this->decodeResult(Session::getFlash('scan_result'));
        $showCmsLinks = AdminAuth::isAdmin();

        require __DIR__ . '/../Views/Admin/scan.php';
    }

    public function scan(): void
    {
        TicketScannerAuth::requireScannerAccess();

        if (!Csrf::validate('scan', $_POST['_csrf'] ?? null)) {
            Session::setFlash('scan_error', 'Invalid security token. Try again.');
            header('Location: /admin/scan');
            exit;
        }

        $result = $this->ticketScanService->scanInput($_POST);
        Session::setFlash('scan_result', json_encode($result));
        header('Location: /admin/scan');
        exit;
    }

    // decode the flashed scan result json back into an array for the view
    private function decodeResult(mixed $raw): ?array
    {
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }
}
