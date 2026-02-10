<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEventTypesTable extends AbstractMigration
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
        $table = $this->table('event_types', ['id' => false, 'primary_key' => 'event_type_id']);

        $table->addColumn('event_type_id', 'integer', ['identity' => true])
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => false])
            ->addIndex(['name'], ['unique' => true])
            ->create();

        $this->table('event_types')->insert([
            ['name' => 'dance', 'description' => 'Dance performances'],
            ['name' => 'jazz', 'description' => 'Jazz concerts'],
            ['name' => 'history', 'description' => 'Historical tours'],
            ['name' => 'yammy', 'description' => 'Food events'],
            ['name' => 'stories', 'description' => 'Storytelling events']
        ])->saveData();
    }
}
