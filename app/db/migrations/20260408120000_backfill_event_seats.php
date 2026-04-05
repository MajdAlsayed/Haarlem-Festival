<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Ticket caps use events.seats; seeders often left it NULL so nothing enforced.
 * Sets a reasonable default only where seats is still NULL.
 */
final class BackfillEventSeats extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            'UPDATE events SET seats = 150
             WHERE seats IS NULL
               AND price IS NOT NULL AND CAST(price AS DECIMAL(10,2)) > 0'
        );
        $this->execute(
            'UPDATE events e
             INNER JOIN event_types et ON et.event_type_id = e.event_type_id
             SET e.seats = 150
             WHERE e.seats IS NULL
               AND LOWER(et.name) IN (\'dance\', \'jazz\', \'history\', \'stories\')'
        );
    }

    public function down(): void
    {
        // Cannot safely restore previous NULLs
    }
}
