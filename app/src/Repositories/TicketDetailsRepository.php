<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * One table holds every sellable thing: single-event tickets, day passes, all-access passes, etc.
 *
 * This class is what admin uses to list, create, update, and force-delete rows. When you delete something that
 * already appeared on an old order, we don’t break accounting — those lines get pointed at a hidden “archive” row instead.
 */
final class TicketDetailsRepository
{
    public const ARCHIVE_PLACEHOLDER_NAME = '[SYSTEM] Archived catalog item';

    public const ARCHIVE_PLACEHOLDER_CATEGORY = 'internal';

    /**
     * Single catalog row by primary key — used by admin edit and archive checks.
     *
     * @return ?array<string,mixed>
     */
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT ticket_details_id, event_id, session_id, ticket_type, category, pass_day, pass_time,
                    schedule_display, sort_order, is_free, name, description, price
             FROM ticket_details WHERE ticket_details_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        return $r ?: null;
    }

    /**
     * Everything in the shop catalog with joined event title/type for the admin table.
     *
     * @return list<array<string,mixed>>
     */
    public function listAllForAdmin(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
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

    /**
     * Event tickets for a public event detail page (Buy tickets).
     *
     * @return list<array<string,mixed>>
     */
    public function listByEventIdForPublic(int $eventId): array
    {
        if ($eventId <= 0) {
            return [];
        }
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT ticket_details_id, name, description, price, is_free, sort_order
             FROM ticket_details
             WHERE event_id = :eid AND ticket_type = 'event_ticket'
             ORDER BY sort_order ASC, ticket_details_id ASC"
        );
        $stmt->execute(['eid' => $eventId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    /** Creates a new sellable row — returns the new `ticket_details_id`. */
    public function insert(array $row): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO ticket_details (event_id, session_id, ticket_type, category, pass_day, pass_time,
             schedule_display, sort_order, is_free, name, description, price)
             VALUES (:eid, :sid, :tt, :cat, :pd, :pt, :sd, :so, :free, :name, :desc, :price)'
        );
        $stmt->execute([
            'eid' => $row['event_id'],
            'sid' => $row['session_id'],
            'tt' => $row['ticket_type'],
            'cat' => $row['category'] ?? 'all',
            'pd' => $row['pass_day'] ?? null,
            'pt' => $row['pass_time'] ?? null,
            'sd' => $row['schedule_display'] ?? null,
            'so' => (int) ($row['sort_order'] ?? 0),
            'free' => !empty($row['is_free']) ? 1 : 0,
            'name' => $row['name'],
            'desc' => $row['description'] ?? null,
            'price' => $row['price'],
        ]);

        return (int) $db->lastInsertId();
    }

    /**
     * Food reservation helper: creates a ticket_details row linked to reservation_id
     * so booking fees can be processed through the shared cart/checkout flow.
     */
    public function createForReservation(
        int $reservationId,
        string $name,
        string $description,
        float $price
    ): int {
        $db = Database::getConnection();
        $stmt = $db->prepare(
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

        return (int) $db->lastInsertId();
    }

    /** Overwrites an existing catalog row (admin save). */
    public function update(int $id, array $row): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE ticket_details SET event_id = :eid, session_id = :sid, ticket_type = :tt, category = :cat,
             pass_day = :pd, pass_time = :pt, schedule_display = :sd, sort_order = :so, is_free = :free,
             name = :name, description = :desc, price = :price
             WHERE ticket_details_id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'eid' => $row['event_id'],
            'sid' => $row['session_id'],
            'tt' => $row['ticket_type'],
            'cat' => $row['category'] ?? 'all',
            'pd' => $row['pass_day'] ?? null,
            'pt' => $row['pass_time'] ?? null,
            'sd' => $row['schedule_display'] ?? null,
            'so' => (int) ($row['sort_order'] ?? 0),
            'free' => !empty($row['is_free']) ? 1 : 0,
            'name' => $row['name'],
            'desc' => $row['description'] ?? null,
            'price' => $row['price'],
        ]);
    }

    /** Raw DELETE — prefer {@see adminForceDelete()} in admin so orders stay valid. */
    public function delete(int $id): void
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM ticket_details WHERE ticket_details_id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** True for the hidden “[SYSTEM] Archived…” row you must never edit or delete. */
    public function isArchivePlaceholderId(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $row = $this->findById($id);
        if ($row === null) {
            return false;
        }

        return strcasecmp(trim((string) ($row['category'] ?? '')), self::ARCHIVE_PLACEHOLDER_CATEGORY) === 0
            && (string) ($row['name'] ?? '') === self::ARCHIVE_PLACEHOLDER_NAME;
    }

    /**
     * Hidden row for order_items after a real catalog ticket row is removed.
     */
    public function getArchivePlaceholderId(): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
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

    /**
     * Remove a catalog ticket: reassign existing order lines to the archive placeholder,
     * remove cart and personal-program rows, then delete the ticket_details row.
     *
     * @return int Number of order lines reassigned
     */
    public function adminForceDelete(int $id): int
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('Invalid ticket id.');
        }

        $placeholderId = $this->getArchivePlaceholderId();
        if ($id === $placeholderId) {
            throw new \RuntimeException('Cannot delete the system archive placeholder row.');
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $cntStmt = $db->prepare('SELECT COUNT(*) FROM order_items WHERE ticket_details_id = :id');
            $cntStmt->execute(['id' => $id]);
            $reassigned = (int) $cntStmt->fetchColumn();

            $db->prepare(
                'UPDATE order_items SET ticket_details_id = :p WHERE ticket_details_id = :id'
            )->execute(['p' => $placeholderId, 'id' => $id]);

            $db->prepare('DELETE FROM cart_items WHERE ticket_details_id = :id')->execute(['id' => $id]);

            if ($this->databaseHasTable($db, 'personal_program_items')) {
                $db->prepare('DELETE FROM personal_program_items WHERE ticket_details_id = :id')->execute(['id' => $id]);
            }

            if ($this->databaseTableHasColumn($db, 'history_tours', 'ticket_details_id')) {
                $db->prepare('UPDATE history_tours SET ticket_details_id = NULL WHERE ticket_details_id = :id')->execute(['id' => $id]);
            }

            $del = $db->prepare('DELETE FROM ticket_details WHERE ticket_details_id = :id');
            $del->execute(['id' => $id]);
            if ($del->rowCount() === 0) {
                $db->rollBack();
                throw new \RuntimeException('Ticket not found.');
            }

            $db->commit();

            return $reassigned;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /** Feature-detect optional tables before running deletes/updates in a portable way. */
    private function databaseHasTable(PDO $db, string $table): bool
    {
        $stmt = $db->prepare(
            'SELECT 1 FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = :t LIMIT 1'
        );
        $stmt->execute(['t' => $table]);

        return (bool) $stmt->fetchColumn();
    }

    private function databaseTableHasColumn(PDO $db, string $table, string $column): bool
    {
        $stmt = $db->prepare(
            'SELECT 1 FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :t AND column_name = :c LIMIT 1'
        );
        $stmt->execute(['t' => $table, 'c' => $column]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Events that can have an event_ticket (for admin dropdown).
     *
     * @return list<array{event_id:int,title:string,cat:string}>
     */
    public function listEventsForTicketForm(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT e.event_id, e.title, LOWER(et.name) AS cat
             FROM events e
             JOIN event_types et ON et.event_type_id = e.event_type_id
             WHERE LOWER(et.name) IN ('jazz','dance','history','stories')
             ORDER BY et.name ASC, e.event_day ASC, e.start_time ASC, e.title ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Events that still need a ticket_details row — handy callouts on the admin form.
     *
     * @return list<array{event_id:int,title:string}>
     */
    public function listEventsWithoutTicket(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT e.event_id, e.title FROM events e
             JOIN event_types et ON et.event_type_id = e.event_type_id
             LEFT JOIN ticket_details td ON td.event_id = e.event_id
             WHERE td.ticket_details_id IS NULL
               AND LOWER(et.name) IN ('jazz','dance','history','stories')
             ORDER BY e.title ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
