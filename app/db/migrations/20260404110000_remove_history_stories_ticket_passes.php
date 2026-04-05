<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveHistoryStoriesTicketPasses extends AbstractMigration
{
    public function change(): void
    {
        $this->execute(
            "DELETE FROM ticket_details
             WHERE ticket_type IN ('day_pass', 'all_access_pass')
               AND LOWER(category) IN ('history', 'stories')"
        );
    }
}
