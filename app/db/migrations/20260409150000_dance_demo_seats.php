<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** One dance row on /tickets?cat=dance gets a low seat cap so badges show (same pattern as Gumbo Kings on jazz). */
final class DanceDemoSeats extends AbstractMigration
{
    public function up(): void
    {
        $did = $this->fetchRow("SELECT event_type_id FROM event_types WHERE LOWER(name) = 'dance' LIMIT 1");
        if ($did === null || !isset($did['event_type_id'])) {
            return;
        }
        $danceType = (int) $did['event_type_id'];

        $this->execute(
            "UPDATE events SET seats = 6
             WHERE event_type_id = {$danceType}
               AND LOWER(COALESCE(event_day, '')) = 'friday'
               AND title LIKE '%Nicky Romero%'
               AND title LIKE '%Back2Back%'
             LIMIT 1"
        );

        $this->execute(
            "UPDATE events e
             INNER JOIN (
                 SELECT e2.event_id AS eid FROM events e2
                 INNER JOIN ticket_details td ON td.event_id = e2.event_id AND td.ticket_type = 'event_ticket'
                 WHERE e2.event_type_id = {$danceType}
                 ORDER BY e2.event_id ASC
                 LIMIT 1
             ) pick ON pick.eid = e.event_id
             SET e.seats = 6"
        );
    }

    public function down(): void
    {
    }
}
