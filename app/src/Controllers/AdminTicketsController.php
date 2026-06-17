<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\CartRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketDetailsRepository;
use App\Repositories\TicketRepository;
use App\Repositories\TicketsRepository;
use App\Services\AdminTicketsService;
use App\Services\SettingsService;
use App\Services\TicketAvailabilityService;

// ticket shop catalog admin
final class AdminTicketsController
{
    private AdminTicketsService $tickets;

    public function __construct()
    {
        $this->tickets = new AdminTicketsService(
            new TicketDetailsRepository(),
            new TicketsRepository(),
            new SettingsService(new SettingsRepository()),
            new CartRepository(),
            new TicketAvailabilityService(new CartRepository(), new TicketRepository()),
        );
    }

    // list all sellable rows + stock numbers
    public function index(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $data = $this->tickets->getCatalogPageData();
        $app = $data['app'];
        $rows = $data['rows'];
        $capacities = $data['capacities'];
        $stock = $data['stock'];
        $bulkDeleteCsrf = Csrf::token('admin_tickets_bulk_delete');

        require __DIR__ . '/../Views/Admin/Tickets/tickets-list.php';
    }

    public function settings(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->saveSettings();
            return;
        }

        $data = $this->tickets->getSettingsPageData();
        $app = $data['app'];
        $intro = $data['intro'];
        $csrf = Csrf::token('admin_tickets_settings');
        $success = Session::getFlash('admin_success');
        $error = Session::getFlash('admin_error');

        require __DIR__ . '/../Views/Admin/Tickets/tickets-settings.php';
    }

    public function edit(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /admin/tickets');
            exit;
        }

        try {
            $data = $this->tickets->getEditPageData($id);
        } catch (NotFoundException|ValidationException $e) {
            Session::setFlash('admin_error', $e->getMessage());
            header('Location: /admin/tickets');
            exit;
        }

        $this->renderForm($data, Csrf::token('admin_tickets_item'));
    }

    public function newTicket(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $this->renderForm($this->tickets->getNewPageData(), Csrf::token('admin_tickets_item'));
    }

    public function save(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /admin/tickets');
            exit;
        }

        if (!Csrf::validate('admin_tickets_item', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/tickets');
            exit;
        }

        $id = (int) ($_POST['ticket_details_id'] ?? 0);
        $redirect = $id > 0 ? '/admin/tickets/edit?id=' . $id : '/admin/tickets/new';

        try {
            $this->tickets->saveTicket($_POST);
            Session::setFlash('admin_success', 'Ticket saved.');
            header('Location: /admin/tickets');
            exit;
        } catch (ValidationException $e) {
            Session::setFlash('admin_error', $e->getMessage());
            header('Location: ' . $redirect);
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', $e->getMessage());
            header('Location: ' . $redirect);
            exit;
        }
    }

    public function delete(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
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
        try {
            Session::setFlash('admin_success', $this->tickets->deleteTicket($id));
        } catch (ValidationException $e) {
            Session::setFlash('admin_error', $e->getMessage());
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', $e->getMessage());
        }

        header('Location: /admin/tickets');
        exit;
    }

    public function deleteBulk(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /admin/tickets');
            exit;
        }

        if (!Csrf::validate('admin_tickets_bulk_delete', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/tickets');
            exit;
        }

        $raw = $_POST['ticket_details_id'] ?? [];
        $ids = is_array($raw) ? $raw : [];
        $result = $this->tickets->deleteBulkTickets($ids);

        if ($result['deleted'] > 0) {
            $message = $result['deleted'] . ' ticket(s) removed from the catalog.';
            if ($result['reassigned'] > 0) {
                $message .= ' ' . $result['reassigned'] . ' existing order line(s) now use the internal archive placeholder.';
            }
            Session::setFlash('admin_success', $message);
        }

        if ($result['failures'] !== []) {
            Session::setFlash('admin_error', implode(' ', $result['failures']));
        }

        if ($result['error'] !== null) {
            Session::setFlash('admin_error', $result['error']);
        }

        header('Location: /admin/tickets');
        exit;
    }

    private function saveSettings(): void
    {
        if (!Csrf::validate('admin_tickets_settings', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request.');
            header('Location: /admin/tickets/settings');
            exit;
        }

        try {
            $this->tickets->saveIntroText((string) ($_POST['tickets_intro'] ?? ''));
            Session::setFlash('admin_success', 'Tickets page intro saved.');
        } catch (ValidationException $e) {
            Session::setFlash('admin_error', $e->getMessage());
        }

        header('Location: /admin/tickets/settings');
        exit;
    }

    private function renderForm(array $data, string $csrf): void
    {
        $app = $data['app'];
        $row = $data['row'];
        $allEvents = $data['allEvents'];
        $eventsMissing = $data['eventsMissing'];
        $capacityMeta = $data['capacityMeta'];

        require __DIR__ . '/../Views/Admin/Tickets/tickets-edit.php';
    }
}
