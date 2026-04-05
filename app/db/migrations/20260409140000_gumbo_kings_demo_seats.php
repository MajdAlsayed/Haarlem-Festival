<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** Ensures one obvious /tickets?cat=jazz card shows stock badges (migration 091300 may have missed or been skipped). */
final class GumboKingsDemoSeats extends AbstractMigration
{
    public function up(): void
    {
        $jid = $this->fetchRow("SELECT event_type_id FROM event_types WHERE LOWER(name) = 'jazz' LIMIT 1");
        if ($jid === null || !isset($jid['event_type_id'])) {
            return;
        }
        $jazzType = (int) $jid['event_type_id'];
        $this->execute(
            "UPDATE events SET seats = 6
             WHERE event_type_id = {$jazzType}
               AND LOWER(COALESCE(event_day, '')) = 'thursday'
               AND title LIKE '%Gumbo Kings%'
               AND COALESCE(price, 0) > 0
             LIMIT 1"
        );
    }

    public function down(): void
    {
    }
}
