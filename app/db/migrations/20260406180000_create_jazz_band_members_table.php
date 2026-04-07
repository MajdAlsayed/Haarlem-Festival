<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * CMS band members for jazz artist pages (Gumbo Kings, Gare du Nord, etc.).
 */
final class CreateJazzBandMembersTable extends AbstractMigration
{
    public function change(): void
    {
        if ($this->hasTable('jazz_band_members')) {
            return;
        }

        $table = $this->table('jazz_band_members', ['id' => false, 'primary_key' => 'member_id']);
        $table
            ->addColumn('member_id', 'integer', ['identity' => true])
            ->addColumn('artist_slug', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('role', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('image_file', 'string', [
                'limit' => 512,
                'null' => false,
                'comment' => 'Filename or path under public/images/jazz/',
            ])
            ->addColumn('sort_order', 'integer', ['default' => 0, 'null' => false])
            ->addIndex(['artist_slug'], ['name' => 'idx_jazz_band_members_slug'])
            ->create();

        $this->table('jazz_band_members')->insert([
            [
                'artist_slug' => 'gumbo-kings',
                'name' => 'Boy Vielvoije',
                'role' => 'Vocals and harmonica',
                'image_file' => 'Boy veilvoije.png',
                'sort_order' => 0,
            ],
            [
                'artist_slug' => 'gumbo-kings',
                'name' => 'Marc Jansen',
                'role' => 'Guitar and vocals',
                'image_file' => 'Marc jansen.png',
                'sort_order' => 1,
            ],
            [
                'artist_slug' => 'gumbo-kings',
                'name' => 'Thomas Hanenburg',
                'role' => 'Keyboards',
                'image_file' => 'Thomas Hanenburg.png',
                'sort_order' => 2,
            ],
            [
                'artist_slug' => 'gumbo-kings',
                'name' => 'Jonne Venmans',
                'role' => 'Bass guitar',
                'image_file' => 'Jonne venmans.png',
                'sort_order' => 3,
            ],
            [
                'artist_slug' => 'gumbo-kings',
                'name' => 'Remon Hubert',
                'role' => 'Drums',
                'image_file' => 'Remon Hubert.png',
                'sort_order' => 4,
            ],
            [
                'artist_slug' => 'gare-du-nord',
                'name' => 'Leona Philippo',
                'role' => 'Lead Vocals',
                'image_file' => 'hero-gare-du-nord.jpg',
                'sort_order' => 0,
            ],
            [
                'artist_slug' => 'gare-du-nord',
                'name' => 'Aron Raams',
                'role' => 'Guitar & Vocals',
                'image_file' => 'Gare-du-nord-event.png',
                'sort_order' => 1,
            ],
            [
                'artist_slug' => 'gare-du-nord',
                'name' => 'Marc Schenk',
                'role' => 'Drums',
                'image_file' => 'hero-gare-du-nord.jpg',
                'sort_order' => 2,
            ],
            [
                'artist_slug' => 'gare-du-nord',
                'name' => 'Ferry Lagendijk',
                'role' => 'Keys',
                'image_file' => 'Gare-du-nord-event.png',
                'sort_order' => 3,
            ],
            [
                'artist_slug' => 'gare-du-nord',
                'name' => 'Sven Happel',
                'role' => 'Bass',
                'image_file' => 'hero-gare-du-nord.jpg',
                'sort_order' => 4,
            ],
            [
                'artist_slug' => 'gare-du-nord',
                'name' => 'Miguel Boelens',
                'role' => 'Saxophone',
                'image_file' => 'Gare-du-nord-event.png',
                'sort_order' => 5,
            ],
        ])->save();
    }
}
