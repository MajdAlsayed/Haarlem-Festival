<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

final class TicketDetailsRepository extends Repository
{
    public const ARCHIVE_PLACEHOLDER_NAME = '[SYSTEM] Archived catalog item';

    public const ARCHIVE_PLACEHOLDER_CATEGORY = 'internal';

    private const SELECT_COLUMNS = 'ticket_details_id, event_id, session_id, ticket_type, category, pass_day, pass_time,
                    schedule_display, sort_order, is_free, name, description, price';

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT ' . self::SELECT_COLUMNS . '
             FROM ticket_details WHERE ticket_details_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function listAllForAdmin(): array
    {
        $stmt = $this->db->query(
            'SELECT td.ticket_details_id, td.event_id, td.session_id, td.ticket_type, td.category, td.pass_day, td.pass_time,
                    td.schedule_display, td.sort_order, td.is_free, td.name, td.description, td.price,
                    e.title AS event_title,
                    LOWER(et.name) AS event_type_name
             FROM ticket_details td
             LEFT JOIN events e ON e.event_id = td.event_id
             LEFT JOIN event_types et ON et.event_type_id = e.event_type_id
             ORDER BY td.ticket_type ASC, td.category ASC, td.sort_order ASC, td.ticket_details_id ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByEventIdForPublic(int $eventId): array
    {
        if ($eventId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT ticket_details_id, name, description, price, is_free, sort_order
             FROM ticket_details
             WHERE event_id = :eid AND ticket_type = 'event_ticket'
             ORDER BY sort_order ASC, ticket_details_id ASC"
        );
        $stmt->execute(['eid' => $eventId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(array $row): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ticket_details (event_id, session_id, ticket_type, category, pass_day, pass_time,
             schedule_display, sort_order, is_free, name, description, price)
             VALUES (:eid, :sid, :tt, :cat, :pd, :pt, :sd, :so, :free, :name, :desc, :price)'
        );
        $stmt->execute($this->writeValues($row));

        return (int) $this->db->lastInsertId();
    }

    public function createForReservation(
        int $reservationId,
        string $name,
        string $description,
        float $price
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO ticket_details (
                reservation_id, event_id, session_id, ticket_type, category, pass_day, pass_time,
                schedule_display, sort_order, is_free, name, description, price
            ) VALUES (
                :rid, NULL, NULL, :tt, :cat, NULL, NULL, NULL, :so, :free, :name, :desc, :price
            )'
        );
        $stmt->execute([
            'rid' => $reservationId,
            'tt' => 'event_ticket',
            'cat' => 'food',
            'so' => 0,
            'free' => $price <= 0 ? 1 : 0,
            'name' => $name,
            'desc' => $description !== '' ? $description : null,
            'price' => number_format($price, 2, '.', ''),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $row): void
    {
        $stmt = $this->db->prepare(
            'UPDATE ticket_details SET event_id = :eid, session_id = :sid, ticket_type = :tt, category = :cat,
             pass_day = :pd, pass_time = :pt, schedule_display = :sd, sort_order = :so, is_free = :free,
             name = :name, description = :desc, price = :price
             WHERE ticket_details_id = :id'
        );
        $stmt->execute([
            'id' => $id,
            ...$this->writeValues($row),
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM ticket_details WHERE ticket_details_id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function isArchivePlaceholderId(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $row = $this->findById($id);
        if ($row === null) {
            return false;
        }

        return strcasecmp(trim($this->rowText($row, 'category')), self::ARCHIVE_PLACEHOLDER_CATEGORY) === 0
            && $this->rowText($row, 'name') === self::ARCHIVE_PLACEHOLDER_NAME;
    }

    // hidden system row for deleted catalog items
    public function getArchivePlaceholderId(): int
    {
        $stmt = $this->db->prepare(
            'SELECT ticket_details_id FROM ticket_details
             WHERE name = :n AND LOWER(TRIM(category)) = :c LIMIT 1'
        );
        $stmt->execute([
            'n' => self::ARCHIVE_PLACEHOLDER_NAME,
            'c' => self::ARCHIVE_PLACEHOLDER_CATEGORY,
        ]);
        $found = $stmt->fetchColumn();
        if ($found !== false && (int) $found > 0) {
            return (int) $found;
        }

        return $this->insert([
            'event_id' => null,
            'session_id' => null,
            'ticket_type' => 'event_ticket',
            'category' => self::ARCHIVE_PLACEHOLDER_CATEGORY,
            'pass_day' => null,
            'pass_time' => null,
            'schedule_display' => null,
            'sort_order' => 9999,
            'is_free' => true,
            'name' => self::ARCHIVE_PLACEHOLDER_NAME,
            'description' => 'Placeholder for historical order lines after a ticket was removed from the catalog by an admin.',
            'price' => '0.00',
        ]);
    }

    // old order lines go to archive placeholder
    public function adminForceDelete(int $id): int
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Invalid ticket id.');
        }

        $placeholderId = $this->getArchivePlaceholderId();
        if ($id === $placeholderId) {
            throw new RuntimeException('Cannot delete the system archive placeholder row.');
        }

        $this->db->beginTransaction();
        try {
            $cntStmt = $this->db->prepare('SELECT COUNT(*) FROM order_items WHERE ticket_details_id = :id');
            $cntStmt->execute(['id' => $id]);
            $reassigned = (int) $cntStmt->fetchColumn();

            $this->db->prepare(
                'UPDATE order_items SET ticket_details_id = :p WHERE ticket_details_id = :id'
            )->execute(['p' => $placeholderId, 'id' => $id]);

            $this->db->prepare('DELETE FROM cart_items WHERE ticket_details_id = :id')->execute(['id' => $id]);

            if ($this->databaseHasTable('personal_program_items')) {
                $this->db->prepare('DELETE FROM personal_program_items WHERE ticket_details_id = :id')->execute(['id' => $id]);
            }

            if ($this->databaseTableHasColumn('history_tours', 'ticket_details_id')) {
                $this->db->prepare('UPDATE history_tours SET ticket_details_id = NULL WHERE ticket_details_id = :id')->execute(['id' => $id]);
            }

            $del = $this->db->prepare('DELETE FROM ticket_details WHERE ticket_details_id = :id');
            $del->execute(['id' => $id]);
            if ($del->rowCount() === 0) {
                $this->db->rollBack();
                throw new RuntimeException('Ticket not found.');
            }

            $this->db->commit();

            return $reassigned;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function listEventsForTicketForm(): array
    {
        $stmt = $this->db->query(
            "SELECT e.event_id, e.title, LOWER(et.name) AS cat
             FROM events e
             JOIN event_types et ON et.event_type_id = e.event_type_id
             WHERE LOWER(et.name) IN ('jazz','dance','history','stories')
             ORDER BY et.name ASC, e.event_day ASC, e.start_time ASC, e.title ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listEventsWithoutTicket(): array
    {
        $stmt = $this->db->query(
            "SELECT e.event_id, e.title FROM events e
             JOIN event_types et ON et.event_type_id = e.event_type_id
             LEFT JOIN ticket_details td ON td.event_id = e.event_id
             WHERE td.ticket_details_id IS NULL
               AND LOWER(et.name) IN ('jazz','dance','history','stories')
             ORDER BY e.title ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventCategoryByEventId(int $eventId): ?string
    {
        if ($eventId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT LOWER(et.name) AS n FROM events e
             INNER JOIN event_types et ON et.event_type_id = e.event_type_id
             WHERE e.event_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $eventId]);
        $name = $stmt->fetchColumn();

        return is_string($name) ? strtolower($name) : null;
    }

    private function writeValues(array $row): array
    {
        return [
            'eid' => $this->rowValue($row, 'event_id'),
            'sid' => $this->rowValue($row, 'session_id'),
            'tt' => $row['ticket_type'],
            'cat' => $this->categoryValue($row),
            'pd' => $this->rowValue($row, 'pass_day'),
            'pt' => $this->rowValue($row, 'pass_time'),
            'sd' => $this->rowValue($row, 'schedule_display'),
            'so' => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
            'free' => !empty($row['is_free']) ? 1 : 0,
            'name' => $row['name'],
            'desc' => $this->rowValue($row, 'description'),
            'price' => $row['price'],
        ];
    }

    private function categoryValue(array $row): string
    {
        if (!isset($row['category'])) {
            return 'all';
        }

        $category = (string) $row['category'];

        return $category !== '' ? $category : 'all';
    }

    private function databaseHasTable(string $table): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = :t LIMIT 1'
        );
        $stmt->execute(['t' => $table]);

        return (bool) $stmt->fetchColumn();
    }

    private function databaseTableHasColumn(string $table, string $column): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :t AND column_name = :c LIMIT 1'
        );
        $stmt->execute(['t' => $table, 'c' => $column]);

        return (bool) $stmt->fetchColumn();
    }

    private function rowText(array $row, string $key): string
    {
        return isset($row[$key]) ? (string) $row[$key] : '';
    }

    private function rowValue(array $row, string $key): mixed
    {
        return array_key_exists($key, $row) ? $row[$key] : null;
    }
}
