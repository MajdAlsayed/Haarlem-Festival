<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateCoordinatesPrecisionInHistoryLocations extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('history_locations');
        $table->changeColumn('lat', 'decimal', ['precision' => 8, 'scale' => 4, 'null' => true, 'default' => null])
            ->changeColumn('lng', 'decimal', ['precision' => 8, 'scale' => 4, 'null' => true, 'default' => null])
            ->update();
    }
}
