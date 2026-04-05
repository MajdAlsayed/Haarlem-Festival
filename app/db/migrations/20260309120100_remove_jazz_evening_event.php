<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Remove the generic "Jazz Evening" event (replaced by JazzSeeder data).
 */
final class RemoveJazzEveningEvent extends AbstractMigration
{
    public function up(): void
    {
        $sql = "
            DELETE e FROM events e
            JOIN event_types et ON e.event_type_id = et.event_type_id
            WHERE LOWER(et.name) = 'jazz'
              AND e.title = 'Jazz Evening'
            LIMIT 1
        ";
        $this->execute($sql);
    }

    public function down(): void
    {
        // Cannot restore deleted row; no-op
    }
}
