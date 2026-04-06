<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTicketFamilyIdToHistoryTours extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('history_tours');

        $table->addColumn('ticket_family_id', 'integer', [
            'null' => true,
            'after' => 'ticket_details_id',
        ]);

        $table->update();
    }
}