<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStoriesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('stories', ['id' => false, 'primary_key' => 'story_id']);

        $table
            ->addColumn('story_id', 'integer', ['identity' => true])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('image_path', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('story_type', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('age', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('language', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('venue_id', 'integer', ['null' => false])
            ->addColumn('event_id', 'integer', ['null' => false])
            ->addIndex(['slug'], ['unique' => true])
            ->addForeignKey('venue_id', 'venues', 'venue_id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('event_id', 'events', 'event_id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();
    }
}
