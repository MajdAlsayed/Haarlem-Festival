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

/**
 * Admin area for the ticket catalog — what actually shows up on /tickets and in cart forms.
 *
 * From here you can create or edit rows in `ticket_details`, tweak the tickets page intro text, or delete items.
 * Deleting is careful: if someone already bought that ticket, old orders keep a hidden “archive” row so money still adds up.
 * Seat limits for a specific gig are usually edited on the Jazz or Dance event screen, not on this list.
 */
final class AdminTicketsController
{
    private const ADMIN_ROLE_ID = 1;

    private TicketDetailsRepository $tickets;
    private SettingsRepository $appSettings;

    /** Wires up the catalog repo and global site settings used by every admin tickets screen. */
    public function __construct()
    {
        $this->tickets = new TicketDetailsRepository();
        $this->appSettings = new SettingsRepository();
    }

    /**
     * Stops the request unless someone is logged in as an admin — otherwise we send them to login or home with a flash.
     */
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

    /** Cached site-wide settings (name, CSS version, …) for the admin layout and views. */
    private function app(): array
    {
        return $this->appSettings->getAll();
    }

    /**
     * Main tickets admin table: every catalog row plus capacity and “how full is it?” badges for the UI.
     */
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
        $bulkDeleteCsrf = Csrf::token('admin_tickets_bulk_delete');
        require __DIR__ . '/../Views/Admin/Tickets/tickets-list.php';
    }

    /**
     * Edit the paragraph that appears at the top of the public /tickets page (stored as `tickets_intro` in site_settings).
     * GET shows the form; POST validates CSRF and saves.
     */
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
        require __DIR__ . '/../Views/Admin/Tickets/tickets-settings.php';
    }

    /**
     * Edit form for one existing catalog row (?id=). Refuses the hidden “archive placeholder” id so accounting stays intact.
     */
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
        if ($this->tickets->isArchivePlaceholderId($id)) {
            Session::setFlash('admin_error', 'The system archive placeholder row cannot be edited.');
            header('Location: /admin/tickets');
            exit;
        }
        $app = $this->app();
        $csrf = Csrf::token('admin_tickets_item');
        $allEvents = $this->tickets->listEventsForTicketForm();
        $eventsMissing = $this->tickets->listEventsWithoutTicket();
        $capacityMeta = $this->buildTicketCapacityMeta($row);
        require __DIR__ . '/../Views/Admin/Tickets/tickets-edit.php';
    }

    /** Same form as edit(), but with an empty row — for creating a new pass or event ticket. */
    public function newTicket(): void
    {
        $this->requireAdmin();
        $app = $this->app();
        $csrf = Csrf::token('admin_tickets_item');
        $row = null;
        $allEvents = $this->tickets->listEventsForTicketForm();
        $eventsMissing = $this->tickets->listEventsWithoutTicket();
        $capacityMeta = null;
        require __DIR__ . '/../Views/Admin/Tickets/tickets-edit.php';
    }

    /**
     * For event-linked tickets only: pulls venue capacity and stock UI snapshot so the edit screen can show “sold out” hints.
     * Passes and all-access bundles skip this — they don’t use per-event capacity here.
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
        if ($id > 0 && $this->tickets->isArchivePlaceholderId($id)) {
            Session::setFlash('admin_error', 'The system archive placeholder row cannot be edited.');
            header('Location: /admin/tickets');
            exit;
        }
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

    /**
     * Removes one catalog row by POST. Uses a dynamic CSRF form name. Old order lines may be reassigned to the archive placeholder.
     */
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
            if ($this->tickets->isArchivePlaceholderId($id)) {
                Session::setFlash('admin_error', 'The system archive placeholder row cannot be deleted.');
            } else {
                try {
                    $reassigned = $this->tickets->adminForceDelete($id);
                    $msg = 'Ticket removed from the catalog.';
                    if ($reassigned > 0) {
                        $msg .= ' ' . $reassigned . ' existing order line(s) now reference the internal archive placeholder '
                            . '(prices on those lines are unchanged; only the product label in admin may show the placeholder name).';
                    }
                    Session::setFlash('admin_success', $msg);
                } catch (\Throwable $e) {
                    Session::setFlash('admin_error', $e->getMessage());
                }
            }
        }
        header('Location: /admin/tickets');
        exit;
    }

    /**
     * Checkbox bulk delete from the list page: same safeguards as {@see delete()} (skips archive id, aggregates reassigned lines, reports per-id failures).
     */
    public function deleteBulk(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tickets');
            exit;
        }
        if (!Csrf::validate('admin_tickets_bulk_delete', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/tickets');
            exit;
        }

        $raw = $_POST['ticket_details_id'] ?? [];
        if (!is_array($raw)) {
            $raw = [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $raw), static fn (int $id): bool => $id > 0)));
        if ($ids === []) {
            Session::setFlash('admin_error', 'No tickets selected.');
            header('Location: /admin/tickets');
            exit;
        }

        $deleted = 0;
        $reassignedTotal = 0;
        $failures = [];

        foreach ($ids as $id) {
            if ($this->tickets->isArchivePlaceholderId($id)) {
                continue;
            }
            try {
                $reassignedTotal += $this->tickets->adminForceDelete($id);
                ++$deleted;
            } catch (\Throwable $e) {
                $failures[] = '#' . $id . ': ' . $e->getMessage();
            }
        }

        if ($deleted > 0) {
            $msg = $deleted . ' ticket(s) removed from the catalog.';
            if ($reassignedTotal > 0) {
                $msg .= ' ' . $reassignedTotal . ' existing order line(s) now use the internal archive placeholder.';
            }
            Session::setFlash('admin_success', $msg);
        }
        if ($failures !== []) {
            Session::setFlash('admin_error', implode(' ', $failures));
        }
        if ($deleted === 0 && $failures === []) {
            Session::setFlash('admin_error', 'Nothing was deleted (only the system row was selected).');
        }

        header('Location: /admin/tickets');
        exit;
    }
}
