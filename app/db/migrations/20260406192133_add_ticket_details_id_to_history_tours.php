<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTicketDetailsIdToHistoryTours extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('history_tours');

        $table->addColumn('ticket_details_id', 'integer', [
            'null' => true,
            'after' => 'tickets_available',
        ]);

        $table->update();
    }
}