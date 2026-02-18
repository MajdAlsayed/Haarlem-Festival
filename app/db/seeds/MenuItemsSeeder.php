<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class MenuItemsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            ['path' => '/', 'label' => 'FESTIVAL', 'sort_order' => 1],
            ['path' => '/jazz', 'label' => 'JAZZ', 'sort_order' => 2],
            ['path' => '/dance', 'label' => 'DANCE', 'sort_order' => 3],
            ['path' => '/food', 'label' => 'FOOD', 'sort_order' => 4],
            ['path' => '/history', 'label' => 'HISTORY', 'sort_order' => 5],
            ['path' => '/stories', 'label' => 'STORIES', 'sort_order' => 6],
            ['path' => '/program', 'label' => 'PROGRAM', 'sort_order' => 7],
            ['path' => '/tickets', 'label' => 'TICKETS', 'sort_order' => 8],
        ];
        $this->table('menu_items')->insert($data)->saveData();
    }
}
