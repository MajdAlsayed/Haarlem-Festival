<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\AdminTicketsServiceInterface;
use App\Contracts\ServiceInterface\SettingsServiceInterface;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\CartRepository;
use App\Repositories\TicketDetailsRepository;
use App\Repositories\TicketsRepository;

// admin ticket catalog — intro text, crud, stock on list
final class AdminTicketsService implements AdminTicketsServiceInterface
{
    private const TICKET_TYPES = ['event_ticket', 'day_pass', 'all_access_pass'];

    public function __construct(
        private TicketDetailsRepository $ticketDetailsRepository,
        private TicketsRepository $ticketsRepository,
        private SettingsServiceInterface $settingsService,
        private CartRepository $cartRepository,
        private TicketAvailabilityService $ticketAvailabilityService,
    ) {
    }

    // header/nav settings for admin layout
    public function appSettings(): array
    {
        return $this->settingsService->getAll();
    }

    // list + capacity + stock for admin table
    public function getCatalogPageData(): array
    {
        $rows = $this->ticketDetailsRepository->listAllForAdmin();
        $ids = $this->ticketIdsFromRows($rows);

        return [
            'app' => $this->appSettings(),
            'rows' => $rows,
            'capacities' => $this->cartRepository->getTicketDetailsCapacitiesForIds($ids),
            'stock' => $this->ticketAvailabilityService->stockUiByTicketDetailsIds($ids),
        ];
    }

    // intro text form above the catalog
    public function getSettingsPageData(): array
    {
        return [
            'app' => $this->appSettings(),
            'intro' => $this->ticketsRepository->getIntroText(),
        ];
    }

    // tickets_intro setting on shop page
    public function saveIntroText(string $text): void
    {
        $text = trim($text);
        if ($text === '') {
            throw new ValidationException('Intro text cannot be empty.');
        }

        if (!$this->settingsService->upsertSetting('tickets_intro', $text)) {
            throw new ValidationException('Could not save intro text.');
        }
    }

    // edit form pre-filled from catalog row
    public function getEditPageData(int $id): array
    {
        $row = $this->loadEditableTicketRow($id);

        return $this->buildFormPageData($row);
    }

    // empty form for new catalog row
    public function getNewPageData(): array
    {
        return $this->buildFormPageData(null);
    }

    // create or update catalog row
    public function saveTicket(array $post): void
    {
        $id = $this->postInt($post, 'ticket_details_id');
        $this->assertNotArchivePlaceholder($id);

        $payload = $this->buildSavePayload($post);

        if ($id > 0) {
            $this->ticketDetailsRepository->update($id, $payload);
            return;
        }

        $this->ticketDetailsRepository->insert($payload);
    }

    // force delete with archive fallback
    public function deleteTicket(int $id): string
    {
        $this->assertDeletableTicket($id);

        $reassigned = $this->ticketDetailsRepository->adminForceDelete($id);
        $message = 'Ticket removed from the catalog.';

        if ($reassigned > 0) {
            $message .= ' ' . $reassigned . ' existing order line(s) now reference the internal archive placeholder '
                . '(prices on those lines are unchanged; only the product label in admin may show the placeholder name).';
        }

        return $message;
    }

    // checkbox delete on catalog list
    public function deleteBulkTickets(array $ids): array
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return $this->bulkDeleteResult(0, 0, [], 'No tickets selected.');
        }

        $deleted = 0;
        $reassignedTotal = 0;
        $failures = [];

        foreach ($ids as $id) {
            if ($this->ticketDetailsRepository->isArchivePlaceholderId($id)) {
                continue;
            }

            try {
                $reassignedTotal += $this->ticketDetailsRepository->adminForceDelete($id);
                ++$deleted;
            } catch (\Throwable $e) {
                $failures[] = '#' . $id . ': ' . $e->getMessage();
            }
        }

        $error = null;
        if ($deleted === 0 && $failures === []) {
            $error = 'Nothing was deleted (only the system row was selected).';
        }

        return $this->bulkDeleteResult($deleted, $reassignedTotal, $failures, $error);
    }

    private function ticketIdsFromRows(array $rows): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn (array $row): int => $this->rowInt($row, 'ticket_details_id'), $rows),
            static fn (int $id): bool => $id > 0,
        )));
    }

    private function loadEditableTicketRow(int $id): array
    {
        if ($id <= 0) {
            throw new NotFoundException('Ticket not found.');
        }

        $row = $this->ticketDetailsRepository->findById($id);
        if ($row === null) {
            throw new NotFoundException('Ticket not found.');
        }

        if ($this->ticketDetailsRepository->isArchivePlaceholderId($id)) {
            throw new ValidationException('The system archive placeholder row cannot be edited.');
        }

        return $row;
    }

    private function assertNotArchivePlaceholder(int $id): void
    {
        if ($id > 0 && $this->ticketDetailsRepository->isArchivePlaceholderId($id)) {
            throw new ValidationException('The system archive placeholder row cannot be edited.');
        }
    }

    private function assertDeletableTicket(int $id): void
    {
        if ($id <= 0) {
            throw new ValidationException('Ticket not found.');
        }

        if ($this->ticketDetailsRepository->isArchivePlaceholderId($id)) {
            throw new ValidationException('The system archive placeholder row cannot be deleted.');
        }
    }

    private function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0,
        )));
    }

    private function bulkDeleteResult(int $deleted, int $reassigned, array $failures, ?string $error): array
    {
        return [
            'deleted' => $deleted,
            'reassigned' => $reassigned,
            'failures' => $failures,
            'error' => $error,
        ];
    }

    private function buildFormPageData(?array $row): array
    {
        return [
            'app' => $this->appSettings(),
            'row' => $row,
            'allEvents' => $this->ticketDetailsRepository->listEventsForTicketForm(),
            'eventsMissing' => $this->ticketDetailsRepository->listEventsWithoutTicket(),
            'capacityMeta' => $row !== null ? $this->buildCapacityMeta($row) : null,
        ];
    }

    private function buildCapacityMeta(array $row): ?array
    {
        if ($this->rowText($row, 'ticket_type') !== 'event_ticket') {
            return null;
        }

        $ticketDetailsId = $this->rowInt($row, 'ticket_details_id');
        if ($ticketDetailsId <= 0) {
            return null;
        }

        $eventId = $this->rowInt($row, 'event_id');
        $stockMap = $this->ticketAvailabilityService->stockUiByTicketDetailsIds([$ticketDetailsId]);
        $stock = isset($stockMap[$ticketDetailsId]) ? $stockMap[$ticketDetailsId] : null;

        return [
            'capacity' => $this->cartRepository->getTicketDetailsCapacity($ticketDetailsId),
            'stock' => $stock,
            'event_id' => $eventId,
            'session_id' => array_key_exists('session_id', $row) ? $row['session_id'] : null,
            'event_type_name' => $this->ticketDetailsRepository->getEventCategoryByEventId($eventId),
        ];
    }

    private function buildSavePayload(array $post): array
    {
        $type = $this->normalizeTicketType($this->postString($post, 'ticket_type', 'event_ticket'));
        $category = $this->normalizeCategory($this->postString($post, 'category', 'all'));
        $name = trim($this->postString($post, 'name'));
        $eventId = $this->postInt($post, 'event_id');

        $this->validateTicketForm($type, $category, $name, $eventId);

        $eventIdValue = $type === 'event_ticket' ? $eventId : null;
        if ($type === 'event_ticket' && $eventIdValue !== null) {
            $category = $this->resolveCategoryFromEvent($category, $eventIdValue);
        }

        $payload = [
            'event_id' => $eventIdValue,
            'session_id' => null,
            'ticket_type' => $type,
            'category' => $category,
            'pass_day' => $this->nullableLowercase($this->postString($post, 'pass_day')),
            'pass_time' => $this->nullableText($this->postString($post, 'pass_time')),
            'schedule_display' => $this->nullableText($this->postString($post, 'schedule_display')),
            'sort_order' => $this->postInt($post, 'sort_order'),
            'is_free' => $this->postIsChecked($post, 'is_free'),
            'name' => $name,
            'description' => $this->nullableText(trim($this->postString($post, 'description'))),
            'price' => $this->formatPrice($this->postString($post, 'price', '0'), $this->postIsChecked($post, 'is_free')),
        ];

        return $this->applyTicketTypeRules($payload, $type);
    }

    private function validateTicketForm(string $type, string $category, string $name, int $eventId): void
    {
        if ($name === '') {
            throw new ValidationException('Name is required.');
        }

        if (in_array($type, ['day_pass', 'all_access_pass'], true)
            && in_array($category, ['history', 'stories'], true)) {
            throw new ValidationException(
                'Day passes and all-access passes are only used on the public Tickets page for Jazz and Dance. '
                . 'For History or Stories, use Event ticket, or set category to Jazz/Dance.',
            );
        }

        if ($type === 'event_ticket' && $eventId <= 0) {
            throw new ValidationException('Event is required for event tickets.');
        }
    }

    private function resolveCategoryFromEvent(string $category, int $eventId): string
    {
        $eventCategory = $this->ticketDetailsRepository->getEventCategoryByEventId($eventId);
        if ($eventCategory !== null && in_array($eventCategory, TicketsRepository::CATEGORIES, true)) {
            return $eventCategory;
        }

        return $category;
    }

    private function formatPrice(string $priceRaw, bool $isFree): string
    {
        if ($isFree) {
            return '0.00';
        }

        if (!is_numeric($priceRaw)) {
            return '0.00';
        }

        return number_format((float) $priceRaw, 2, '.', '');
    }

    private function applyTicketTypeRules(array $payload, string $type): array
    {
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

        return $payload;
    }

    private function normalizeTicketType(string $type): string
    {
        $type = trim($type);

        return in_array($type, self::TICKET_TYPES, true) ? $type : 'event_ticket';
    }

    private function normalizeCategory(string $category): string
    {
        $category = strtolower(trim($category));
        $allowed = array_merge(TicketsRepository::CATEGORIES, ['all']);

        return in_array($category, $allowed, true) ? $category : 'all';
    }

    private function nullableText(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    private function nullableLowercase(string $value): ?string
    {
        return $value !== '' ? strtolower($value) : null;
    }

    private function postIsChecked(array $post, string $key): bool
    {
        return !empty($post[$key]);
    }

    private function postString(array $post, string $key, string $default = ''): string
    {
        return isset($post[$key]) ? (string) $post[$key] : $default;
    }

    private function postInt(array $post, string $key, int $default = 0): int
    {
        return isset($post[$key]) ? (int) $post[$key] : $default;
    }

    private function rowText(array $row, string $key): string
    {
        return isset($row[$key]) ? (string) $row[$key] : '';
    }

    private function rowInt(array $row, string $key): int
    {
        return isset($row[$key]) ? (int) $row[$key] : 0;
    }
}
