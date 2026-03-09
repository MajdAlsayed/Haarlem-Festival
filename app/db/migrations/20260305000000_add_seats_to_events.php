<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSeatsToEvents extends AbstractMigration
{
    public function change(): void
    {
        $this->table('events')
            ->addColumn('seats', 'integer', ['null' => true, 'after' => 'hall'])
            ->update();
    }
}
