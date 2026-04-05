<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PayLaterPersonalProgram extends AbstractMigration
{
    public function change(): void
    {
        $this->table('orders')
            ->addColumn('expires_at', 'datetime', ['null' => true, 'after' => 'paid_at'])
            ->addColumn('payment_reminder_sent', 'boolean', ['default' => false, 'after' => 'expires_at'])
            ->update();

        $t = $this->table('personal_program_items', ['id' => false, 'primary_key' => 'program_item_id']);
        $t->addColumn('program_item_id', 'integer', ['identity' => true])
            ->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('ticket_details_id', 'integer', ['null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'user_id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('ticket_details_id', 'ticket_details', 'ticket_details_id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addIndex(['user_id', 'ticket_details_id'], ['unique' => true, 'name' => 'personal_program_user_ticket_uidx'])
            ->create();
    }
}
