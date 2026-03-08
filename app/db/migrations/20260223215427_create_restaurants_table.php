<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRestaurantsTable extends AbstractMigration
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
    $table = $this->table('restaurants', ['id' => false, 'primary_key' => 'restaurant_id']);

        $table
            ->addColumn('restaurant_id', 'integer', ['identity' => true])

            ->addColumn('name', 'string', ['limit' => 150])
            ->addColumn('slug', 'string', ['limit' => 180]) // for clean URLs later
            ->addColumn('address', 'string', ['limit' => 255])
            ->addColumn('type', 'string', ['limit' => 255]) // Dutch, fish and seafood, European...
            ->addColumn('image', 'string', ['limit' => 255, 'null' => true]) // Cafe-de-Roemer.jpg etc.

            ->addColumn('sessions', 'integer') // number of sessions
            ->addColumn('duration_hours', 'decimal', ['precision' => 3, 'scale' => 1]) // 1.5, 2.0
            ->addColumn('first_session', 'time') // 18:00, 17:00 etc.
            ->addColumn('second_session', 'time') // 18:00, 17:00 etc.
            ->addColumn('third_session', 'time') // 18:00, 17:00 etc.


            ->addColumn('stars', 'integer') // 3 / 4
            ->addColumn('seats', 'integer')

            ->addColumn('price_adult', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('price_kid', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('kid_age_max', 'integer', ['default' => 12])

            ->addColumn('walk_minutes_to_patronaat', 'integer', ['null' => true])

            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['null' => true])

            ->addIndex(['name'])
            ->addIndex(['slug'], ['unique' => true])
            ->create();
    }
}
