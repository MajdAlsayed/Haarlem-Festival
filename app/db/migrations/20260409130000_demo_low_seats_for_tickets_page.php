<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Makes 90% / sold-out badges visible without selling hundreds of tickets.
 * Tightens seats on one jazz + one dance event that already have ticket_details rows.
 */
final class DemoLowSeatsForTicketsPage extends AbstractMigration
{
    public function up(): void
    {
        // Prefer a well-known first card on /tickets?cat=jazz (Thursday grid).
        $jazz = $this->fetchRow(
            "SELECT e.event_id FROM events e
             INNER JOIN event_types et ON et.event_type_id = e.event_type_id AND LOWER(et.name) = 'jazz'
             INNER JOIN ticket_details td ON td.event_id = e.event_id AND td.ticket_type = 'event_ticket'
             WHERE COALESCE(e.price, 0) > 0
               AND e.title LIKE '%Gumbo Kings%'
               AND LOWER(COALESCE(e.event_day, '')) = 'thursday'
             LIMIT 1"
        );
        if ($jazz === null || !isset($jazz['event_id'])) {
            $jazz = $this->fetchRow(
                "SELECT e.event_id FROM events e
                 INNER JOIN event_types et ON et.event_type_id = e.event_type_id AND LOWER(et.name) = 'jazz'
                 INNER JOIN ticket_details td ON td.event_id = e.event_id AND td.ticket_type = 'event_ticket'
                 WHERE COALESCE(e.price, 0) > 0
                 ORDER BY e.event_id ASC
                 LIMIT 1"
            );
        }
        if ($jazz !== null && isset($jazz['event_id'])) {
            $this->execute('UPDATE events SET seats = 6 WHERE event_id = ' . (int) $jazz['event_id']);
        }

        $dance = $this->fetchRow(
            "SELECT e.event_id FROM events e
             INNER JOIN event_types et ON et.event_type_id = e.event_type_id AND LOWER(et.name) = 'dance'
             INNER JOIN ticket_details td ON td.event_id = e.event_id AND td.ticket_type = 'event_ticket'
             ORDER BY e.event_id ASC
             LIMIT 1"
        );
        if ($dance !== null && isset($dance['event_id'])) {
            $this->execute('UPDATE events SET seats = 6 WHERE event_id = ' . (int) $dance['event_id']);
        }
    }

    public function down(): void
    {
    }
}
