<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Removes up to 80 duplicate day_pass / all_access_pass rows from the tickets "Special offer" pool.
 * Preserves the 8 lowest-ID pass rows that have no order lines (matches jazz+dance seeder shape).
 * Clears cart / personal program / history tour links for removed IDs first (FK-safe).
 */
final class TrimSpecialOfferPassDuplicates extends AbstractMigration
{
    private const MAX_DELETE = 80;

    private const MIN_PASSES_TO_KEEP = 8;

    public function up(): void
    {
        $conn = $this->getAdapter()->getConnection();

        $stmt = $conn->query(
            "SELECT td.ticket_details_id
             FROM ticket_details td
             WHERE td.ticket_type IN ('day_pass', 'all_access_pass')
               AND NOT (
                   td.name = '[SYSTEM] Archived catalog item'
                   AND LOWER(TRIM(td.category)) = 'internal'
               )
               AND NOT EXISTS (
                   SELECT 1 FROM order_items oi WHERE oi.ticket_details_id = td.ticket_details_id
               )
             ORDER BY td.ticket_details_id ASC"
        );
        /** @var list<array{ticket_details_id: int|string}>|false $rows */
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows === false || $rows === []) {
            return;
        }

        $ids = [];
        foreach ($rows as $r) {
            $id = (int) ($r['ticket_details_id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        if (count($ids) <= self::MIN_PASSES_TO_KEEP) {
            return;
        }

        $protected = array_slice($ids, 0, self::MIN_PASSES_TO_KEEP);
        $protectedSet = array_fill_keys($protected, true);
        $deletable = array_values(array_filter($ids, static fn (int $id): bool => !isset($protectedSet[$id])));
        if ($deletable === []) {
            return;
        }

        rsort($deletable, SORT_NUMERIC);
        $toDelete = array_slice($deletable, 0, self::MAX_DELETE);
        if ($toDelete === []) {
            return;
        }

        $in = implode(',', array_map('intval', $toDelete));

        $conn->exec("DELETE FROM cart_items WHERE ticket_details_id IN ($in)");

        try {
            $conn->exec("DELETE FROM personal_program_items WHERE ticket_details_id IN ($in)");
        } catch (\Throwable) {
            // table may be absent in some envs
        }

        try {
            $conn->exec("UPDATE history_tours SET ticket_details_id = NULL WHERE ticket_details_id IN ($in)");
        } catch (\Throwable) {
        }

        $conn->exec("DELETE FROM ticket_details WHERE ticket_details_id IN ($in)");
    }

    public function down(): void
    {
        // Data removal is not reversible.
    }
}
