<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class PageSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'slug' => 'home',
                'title' => 'Haarlem Festival',
                'is_published' => 1
            ]
        ];

        $this->table('pages')->insert($data)->saveData();
    }
}
