<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Remove Karsu Saturday event (no longer in JazzSeeder).
 */
final class RemoveKarsuSaturday extends AbstractMigration
{
    public function up(): void
    {
        $sql = "
            DELETE e FROM events e
            JOIN event_types et ON e.event_type_id = et.event_type_id
            WHERE LOWER(et.name) = 'jazz'
              AND e.title = 'Karsu'
              AND LOWER(TRIM(COALESCE(e.event_day, ''))) = 'saturday'
            LIMIT 1
        ";
        $this->execute($sql);
    }

    public function down(): void
    {
        // Cannot restore; no-op
    }
}
