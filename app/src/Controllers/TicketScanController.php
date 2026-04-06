<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\TicketScannerAuth;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketRepository;

/**
 * Ticket scanner for admin and employee roles (assessment: employee scans at door).
 */
final class TicketScanController
{
    public function index(): void
    {
        TicketScannerAuth::requireScannerAccess();
        $app = (new SettingsRepository())->getAll();
        $csrf = Csrf::token('scan');
        $error = Session::getFlash('scan_error');
        $resultRaw = Session::getFlash('scan_result');
        $result = null;
        if (is_string($resultRaw) && $resultRaw !== '') {
            $decoded = json_decode($resultRaw, true);
            $result = is_array($decoded) ? $decoded : null;
        }
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

        $groupRaw = trim((string) ($_POST['group_codes'] ?? ''));
        if ($groupRaw !== '') {
            $lines = preg_split('/\r\n|\r|\n/', $groupRaw) ?: [];
            $codes = [];
            foreach ($lines as $line) {
                $c = strtolower(preg_replace('/\s+/', '', $line));
                if ($c !== '') {
                    $codes[] = $c;
                }
            }
            $codes = array_slice(array_values(array_unique($codes)), 0, 4);
            if ($codes === []) {
                Session::setFlash('scan_result', json_encode(['type' => 'empty_batch']));
                header('Location: /admin/scan');
                exit;
            }

            $repo = new TicketRepository();
            $results = [];
            foreach ($codes as $code) {
                $results[] = $this->scanOneCode($repo, $code);
            }
            Session::setFlash('scan_result', json_encode(['type' => 'batch', 'results' => $results]));
            header('Location: /admin/scan');
            exit;
        }

        $code = strtolower(preg_replace('/\s+/', '', (string) ($_POST['ticket_code'] ?? '')));

        if ($code === '') {
            Session::setFlash('scan_result', json_encode(['type' => 'empty']));
            header('Location: /admin/scan');
            exit;
        }

        $repo = new TicketRepository();
        $payload = $this->scanOneCode($repo, $code);
        Session::setFlash('scan_result', json_encode($payload));
        header('Location: /admin/scan');
        exit;
    }

    /**
     * @return array<string, mixed>
     */
    private function scanOneCode(TicketRepository $repo, string $code): array
    {
        $row = $repo->findByCodeWithDetails($code);

        if ($row === null) {
            return ['type' => 'not_found', 'ticket_code' => $code];
        }

        $outcome = $repo->markScannedIfValid((int) $row['ticket_id']);

        return [
            'type' => $outcome,
            'ticket_code' => $code,
            'ticket_name' => (string) ($row['ticket_name'] ?? ''),
            'ticket_type' => (string) ($row['ticket_type'] ?? ''),
            'event_title' => $row['event_title'] !== null ? (string) $row['event_title'] : '',
            'event_day' => $row['event_day'] !== null ? (string) $row['event_day'] : '',
            'code_tail' => strlen($code) > 6 ? substr($code, -6) : $code,
        ];
    }
}
