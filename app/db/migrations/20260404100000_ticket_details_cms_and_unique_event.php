<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TicketDetailsCmsAndUniqueEvent extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('ticket_details');

        if (!$table->hasColumn('category')) {
            $table->addColumn('category', 'string', [
                'limit' => 32,
                'null' => false,
                'default' => 'all',
                'comment' => 'jazz|dance|history|stories|all — tab visibility for passes',
            ]);
        }
        if (!$table->hasColumn('pass_day')) {
            $table->addColumn('pass_day', 'string', ['limit' => 24, 'null' => true]);
        }
        if (!$table->hasColumn('pass_time')) {
            $table->addColumn('pass_time', 'string', ['limit' => 32, 'null' => true]);
        }
        if (!$table->hasColumn('schedule_display')) {
            $table->addColumn('schedule_display', 'string', ['limit' => 255, 'null' => true]);
        }
        if (!$table->hasColumn('sort_order')) {
            $table->addColumn('sort_order', 'integer', ['null' => false, 'default' => 0]);
        }
        if (!$table->hasColumn('is_free')) {
            $table->addColumn('is_free', 'boolean', ['null' => false, 'default' => false]);
        }

        $table->update();

        if ($table->hasColumn('description')) {
            $table->changeColumn('description', 'text', ['null' => true])->update();
        }
    }
}
