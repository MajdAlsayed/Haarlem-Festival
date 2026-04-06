<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;
use App\Repositories\CartRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketDetailsRepository;
use App\Repositories\TicketRepository;
use App\Repositories\TicketsRepository;
use App\Services\TicketAvailabilityService;

final class AdminTicketsController
{
    private const ADMIN_ROLE_ID = 1;

    private TicketDetailsRepository $tickets;
    private SettingsRepository $appSettings;

    public function __construct()
    {
        $this->tickets = new TicketDetailsRepository();
        $this->appSettings = new SettingsRepository();
    }

    private function requireAdmin(): void
    {
        $auth = $_SESSION['auth'] ?? null;
        if (!$auth || empty($auth['user_id'])) {
            Session::setFlash('login_error', 'Please log in to access the admin area.');
            header('Location: /login');
            exit;
        }
        if ((int) ($auth['role_id'] ?? 0) !== self::ADMIN_ROLE_ID) {
            Session::setFlash('login_error', 'You do not have permission to access the admin area.');
            header('Location: /');
            exit;
        }
    }

    private function app(): array
    {
        return $this->appSettings->getAll();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $app = $this->app();
        $rows = $this->tickets->listAllForAdmin();
        $ids = array_values(array_unique(array_filter(array_map('intval', array_column($rows, 'ticket_details_id')))));
        $cartRepo = new CartRepository();
        $ticketRepo = new TicketRepository();
        $capacities = $cartRepo->getTicketDetailsCapacitiesForIds($ids);
        $stock = (new TicketAvailabilityService($cartRepo, $ticketRepo))->stockUiByTicketDetailsIds($ids);
        require __DIR__ . '/../Views/Admin/tickets-list.php';
    }

    public function settings(): void
    {
        $this->requireAdmin();
        $app = $this->app();
        $intro = (new TicketsRepository())->getIntroText();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate('admin_tickets_settings', $_POST['_csrf'] ?? null)) {
                Session::setFlash('admin_error', 'Invalid request.');
                header('Location: /admin/tickets/settings');
                exit;
            }
            $text = trim((string) ($_POST['tickets_intro'] ?? ''));
            if ($text === '') {
                Session::setFlash('admin_error', 'Intro text cannot be empty.');
                header('Location: /admin/tickets/settings');
                exit;
            }
            $db = Database::getConnection();
            $stmt = $db->prepare(
                'INSERT INTO site_settings (setting_key, setting_value) VALUES (\'tickets_intro\', :v)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            $stmt->execute(['v' => $text]);
            Session::setFlash('admin_success', 'Tickets page intro saved.');
            header('Location: /admin/tickets/settings');
            exit;
        }

        $csrf = Csrf::token('admin_tickets_settings');
        $success = Session::getFlash('admin_success');
        $error = Session::getFlash('admin_error');
        require __DIR__ . '/../Views/Admin/tickets-settings.php';
    }

    public function edit(): void
    {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: /admin/tickets');
            exit;
        }
        $row = $this->tickets->findById($id);
        if (!$row) {
            Session::setFlash('admin_error', 'Ticket not found.');
            header('Location: /admin/tickets');
            exit;
        }
        $app = $this->app();
        $csrf = Csrf::token('admin_tickets_item');
        $allEvents = $this->tickets->listEventsForTicketForm();
        $eventsMissing = $this->tickets->listEventsWithoutTicket();
        $capacityMeta = $this->buildTicketCapacityMeta($row);
        require __DIR__ . '/../Views/Admin/tickets-edit.php';
    }

    public function newTicket(): void
    {
        $this->requireAdmin();
        $app = $this->app();
        $csrf = Csrf::token('admin_tickets_item');
        $row = null;
        $allEvents = $this->tickets->listEventsForTicketForm();
        $eventsMissing = $this->tickets->listEventsWithoutTicket();
        $capacityMeta = null;
        require __DIR__ . '/../Views/Admin/tickets-edit.php';
    }

    /**
     * Read-only capacity + stock for event/session tickets (passes have no cap here).
     *
     * @param array<string,mixed> $row
     * @return ?array{capacity: ?int, stock: ?array<string,mixed>, event_id: int, session_id: mixed, event_type_name: ?string}
     */
    private function buildTicketCapacityMeta(array $row): ?array
    {
        if (($row['ticket_type'] ?? '') !== 'event_ticket') {
            return null;
        }
        $tid = (int) ($row['ticket_details_id'] ?? 0);
        if ($tid <= 0) {
            return null;
        }
        $cartRepo = new CartRepository();
        $ticketRepo = new TicketRepository();
        $cap = $cartRepo->getTicketDetailsCapacity($tid);
        $stockMap = (new TicketAvailabilityService($cartRepo, $ticketRepo))->stockUiByTicketDetailsIds([$tid]);
        $eventId = (int) ($row['event_id'] ?? 0);
        $eventTypeName = null;
        if ($eventId > 0) {
            $db = Database::getConnection();
            $st = $db->prepare(
                'SELECT LOWER(et.name) AS n FROM events e
                 INNER JOIN event_types et ON et.event_type_id = e.event_type_id
                 WHERE e.event_id = :id LIMIT 1'
            );
            $st->execute(['id' => $eventId]);
            $n = $st->fetchColumn();
            $eventTypeName = is_string($n) ? strtolower($n) : null;
        }

        return [
            'capacity' => $cap,
            'stock' => $stockMap[$tid] ?? null,
            'event_id' => $eventId,
            'session_id' => $row['session_id'] ?? null,
            'event_type_name' => $eventTypeName,
        ];
    }

    public function save(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tickets');
            exit;
        }
        if (!Csrf::validate('admin_tickets_item', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/tickets');
            exit;
        }

        $id = (int) ($_POST['ticket_details_id'] ?? 0);
        $type = trim((string) ($_POST['ticket_type'] ?? 'event_ticket'));
        if (!in_array($type, ['event_ticket', 'day_pass', 'all_access_pass'], true)) {
            $type = 'event_ticket';
        }
        $category = strtolower(trim((string) ($_POST['category'] ?? 'all')));
        if (!in_array($category, array_merge(TicketsRepository::CATEGORIES, ['all']), true)) {
            $category = 'all';
        }
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $priceRaw = trim((string) ($_POST['price'] ?? '0'));
        $isFree = !empty($_POST['is_free']);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $passDay = trim((string) ($_POST['pass_day'] ?? ''));
        $passTime = trim((string) ($_POST['pass_time'] ?? ''));
        $scheduleDisplay = trim((string) ($_POST['schedule_display'] ?? ''));
        $eventId = (int) ($_POST['event_id'] ?? 0);

        if ($name === '') {
            Session::setFlash('admin_error', 'Name is required.');
            header('Location: ' . ($id > 0 ? '/admin/tickets/edit?id=' . $id : '/admin/tickets/new'));
            exit;
        }

        if (in_array($type, ['day_pass', 'all_access_pass'], true)
            && in_array($category, ['history', 'stories'], true)) {
            Session::setFlash(
                'admin_error',
                'Day passes and all-access passes are only used on the public Tickets page for Jazz and Dance. For History or Stories, use Event ticket, or set category to Jazz/Dance.'
            );
            header('Location: ' . ($id > 0 ? '/admin/tickets/edit?id=' . $id : '/admin/tickets/new'));
            exit;
        }

        $eventIdVal = null;
        if ($type === 'event_ticket') {
            if ($eventId <= 0) {
                Session::setFlash('admin_error', 'Event is required for event tickets.');
                header('Location: ' . ($id > 0 ? '/admin/tickets/edit?id=' . $id : '/admin/tickets/new'));
                exit;
            }
            $eventIdVal = $eventId;
        }

        $price = $isFree ? '0.00' : (is_numeric($priceRaw) ? number_format((float) $priceRaw, 2, '.', '') : '0.00');

        if ($type === 'event_ticket' && $eventIdVal !== null) {
            $db = Database::getConnection();
            $st = $db->prepare(
                'SELECT LOWER(et.name) AS n FROM events e
                 JOIN event_types et ON et.event_type_id = e.event_type_id
                 WHERE e.event_id = :id LIMIT 1'
            );
            $st->execute(['id' => $eventIdVal]);
            $cn = $st->fetchColumn();
            if (is_string($cn) && in_array($cn, TicketsRepository::CATEGORIES, true)) {
                $category = $cn;
            }
        }

        $payload = [
            'event_id' => $eventIdVal,
            'session_id' => null,
            'ticket_type' => $type,
            'category' => $category,
            'pass_day' => $passDay !== '' ? strtolower($passDay) : null,
            'pass_time' => $passTime !== '' ? $passTime : null,
            'schedule_display' => $scheduleDisplay !== '' ? $scheduleDisplay : null,
            'sort_order' => $sortOrder,
            'is_free' => $isFree,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'price' => $price,
        ];

        if ($type === 'event_ticket') {
            $payload['pass_day'] = null;
            $payload['pass_time'] = null;
            $payload['schedule_display'] = null;
        } elseif ($type === 'day_pass') {
            $payload['schedule_display'] = null;
            $payload['event_id'] = null;
        } elseif ($type === 'all_access_pass') {
            $payload['pass_day'] = null;
            $payload['pass_time'] = null;
            $payload['event_id'] = null;
        }

        try {
            if ($id > 0) {
                $this->tickets->update($id, $payload);
            } else {
                $this->tickets->insert($payload);
            }
            Session::setFlash('admin_success', 'Ticket saved.');
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', $e->getMessage());
        }
        header('Location: /admin/tickets');
        exit;
    }

    public function delete(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tickets');
            exit;
        }
        $form = (string) ($_POST['_csrf_form'] ?? '');
        if ($form === '' || !Csrf::validate($form, $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/tickets');
            exit;
        }
        $id = (int) ($_POST['ticket_details_id'] ?? 0);
        if ($id > 0) {
            try {
                $this->tickets->delete($id);
                Session::setFlash('admin_success', 'Ticket deleted.');
            } catch (\Throwable $e) {
                Session::setFlash('admin_error', 'Cannot delete (may be referenced by an order): ' . $e->getMessage());
            }
        }
        header('Location: /admin/tickets');
        exit;
    }
}
