<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Remove duplicate Karsu Friday event (18:30–19:30, 32.00€, 200 seats).
 * Keeps the 18:00–19:00 Karsu Friday slot.
 */
final class RemoveDuplicateKarsuFriday extends AbstractMigration
{
    public function up(): void
    {
        $sql = "
            DELETE e FROM events e
            JOIN event_types et ON e.event_type_id = et.event_type_id
            WHERE LOWER(et.name) = 'jazz'
              AND e.title = 'Karsu'
              AND LOWER(TRIM(COALESCE(e.event_day, ''))) = 'friday'
              AND e.start_time = '18:30'
            LIMIT 1
        ";
        $this->execute($sql);
    }

    public function down(): void
    {
        // Cannot restore deleted row without full data; no-op
    }
}
