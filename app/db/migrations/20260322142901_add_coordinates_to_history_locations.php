<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCoordinatesToHistoryLocations extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('history_locations');
        $table->addColumn('lat', 'decimal', ['precision' => 10, 'scale' => 7, 'null' => true, 'default' => null])
            ->addColumn('lng', 'decimal', ['precision' => 10, 'scale' => 7, 'null' => true, 'default' => null])
            ->update();
    }
}
