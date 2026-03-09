<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seeds artist_photos with gallery image filenames per artist.
 * Run: phinx seed:run -s ArtistPhotosSeeder
 * Add your own image files to app/public/images/dance/Artist/ (tiesto4.png–tiesto7.png, tiesto-hero.png, etc.).
 */
class ArtistPhotosSeeder extends AbstractSeed
{
    private function seedArtistPhotos(\PDO $conn, $adapter, string $slug, array $filenames): void
    {
        $stmt = $conn->prepare('SELECT id FROM artists WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }
        $artistId = (int) $row['id'];
        $adapter->execute('DELETE FROM artist_photos WHERE artist_id = ' . $artistId);
        $rows = [];
        foreach ($filenames as $i => $filename) {
            $rows[] = ['artist_id' => $artistId, 'filename' => $filename, 'sort_order' => $i];
        }
        $this->table('artist_photos')->insert($rows)->saveData();
    }

    public function run(): void
    {
        $adapter = $this->table('artist_photos')->getAdapter();
        $conn = $adapter->getConnection();

        // Hardwell: first = career (hardwell1 – jacket + neon logo); then gallery.
        $this->seedArtistPhotos($conn, $adapter, 'hardwell', [
            'Artist/hardwell1.png',   // career (this one)
            'Artist/hardwell7.png',
            'Artist/hardwell8.png',
            'Artist/hardwell9.png',
        ]);

        // Tiësto: first = career; gallery grid = tiesto4, tiesto5, tiesto6, tiesto7
        $this->seedArtistPhotos($conn, $adapter, 'tiesto', [
            'Artist/tiesto4.png',
            'Artist/tiesto5.png',
            'Artist/tiesto6.png',
            'Artist/tiesto7.png',
        ]);
    }
}
