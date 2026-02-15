<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateLanguagesTable extends AbstractMigration
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
        $table = $this->table('languages', ['id' => false, 'primary_key' => 'language_id']);

        $table
            ->addColumn('language_id', 'integer', ['identity' => true])
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false])
            ->addIndex(['name'], ['unique' => true])
            ->create();

        $this->table('languages')->insert([
            ['name' => 'English'],
            ['name' => 'Dutch'],
            ['name' => 'Chinese']
        ])->saveData();
    }
}
