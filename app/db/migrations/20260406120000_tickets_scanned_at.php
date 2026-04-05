<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TicketsScannedAt extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tickets');
        if (!$table->hasColumn('scanned_at')) {
            $table->addColumn('scanned_at', 'timestamp', ['null' => true, 'after' => 'issued_at'])->update();
        }
    }
}
