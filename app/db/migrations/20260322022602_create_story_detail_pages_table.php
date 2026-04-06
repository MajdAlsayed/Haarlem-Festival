<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStoryDetailPagesTable extends AbstractMigration
{
    public function change(): void
    {
        if ($this->hasTable('story_detail_pages')) {
            return;
        }

        $table = $this->table('story_detail_pages', [
            'id' => false,
            'primary_key' => ['detail_page_id'],
        ]);

        $table
            ->addColumn('detail_page_id', 'integer', [
                'identity' => true,
                'signed' => false,
            ])
            ->addColumn('story_id', 'integer', [
                // Must match stories.story_id (signed INT from create_stories_table).
                'signed' => true,
                'null' => false,
            ])
            ->addColumn('hero_image', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('hero_heading', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('hero_description', 'text', [
                'null' => true,
            ])
            ->addColumn('article_title', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('article_image', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('article_image_caption', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('article_paragraph_1', 'text', [
                'null' => true,
            ])
            ->addColumn('article_paragraph_2', 'text', [
                'null' => true,
            ])
            ->addColumn('article_paragraph_3', 'text', [
                'null' => true,
            ])
            ->addColumn('highlights', 'json', [
                'null' => true,
            ])
            ->addColumn('gallery', 'json', [
                'null' => true,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['story_id'], ['unique' => true])
            ->addForeignKey('story_id', 'stories', 'story_id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->create();
    }
}