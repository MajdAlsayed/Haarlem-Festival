<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RenameDescriptionInHistoryLocations extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('history_locations');

        $table->renameColumn('description', 'description_1')
            ->update();

        $table->addColumn('description_2', 'text', [
            'null' => true,
            'after' => 'description_1'
        ])->update();
    }
}
