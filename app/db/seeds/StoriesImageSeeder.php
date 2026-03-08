<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class StoriesImageSeeder extends AbstractSeed
{
    public function run(): void
    {
        $map = [
            'omdenken-podcast' => '/images/Stories/cards/card-image-4.jpg',
            'the-story-of-buurderij-haarlem' => '/images/Stories/cards/card-image-3.jpg',
            'corrie-voor-kinderen' => '/images/Stories/cards/card-image-1.jpg',
            'winnie-de-poeh' => '/images/Stories/cards/card-image-2.jpg',
        ];

        $pdo = $this->getAdapter()->getConnection();

        foreach ($map as $slug => $img) {
            $this->execute(
                "UPDATE stories
                 SET image_path = " . $pdo->quote($img) . "
                 WHERE slug = " . $pdo->quote($slug)
            );
        }

        echo "[DONE] Updated story images\n";
    }
}