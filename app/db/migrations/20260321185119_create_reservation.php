<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateReservation extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        
        $table = $this->table('reservations', ['id' => false, 'primary_key' => 'reservation_id']);
 
        $table
            ->addColumn('reservation_id',  'integer', ['identity' => true])
            ->addColumn('restaurant_id',   'integer', ['null' => false])
            ->addColumn('user_id',         'integer', ['null' => true])         // null = guest (not logged in)
            ->addColumn('session_time',    'string',  ['limit' => 10,  'null' => false])  // e.g. '18:00'
            ->addColumn('guests',          'integer', ['null' => false])
            ->addColumn('first_name',      'string',  ['limit' => 100, 'null' => false])
            ->addColumn('last_name',       'string',  ['limit' => 100, 'null' => false])
            ->addColumn('email',           'string',  ['limit' => 255, 'null' => false])
            ->addColumn('phone',           'string',  ['limit' => 30,  'null' => true])
            ->addColumn('special_request', 'text',    ['null' => true])
            ->addColumn('status',          'enum',    ['values' => ['pending', 'confirmed', 'cancelled'], 'null' => false, 'default' => 'pending'])
            ->addColumn('reservation_fee', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('created_at',      'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
 
            // indexes for common lookups
            ->addIndex(['restaurant_id'])
            ->addIndex(['user_id'])
            ->addIndex(['email'])
            ->addIndex(['status'])
 
            // foreign keys
            ->addForeignKey('restaurant_id', 'restaurants', 'restaurant_id', ['delete' => 'CASCADE',  'update' => 'CASCADE'])
            ->addForeignKey('user_id',        'users',       'user_id',       ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
 
            ->create();
    }
}
