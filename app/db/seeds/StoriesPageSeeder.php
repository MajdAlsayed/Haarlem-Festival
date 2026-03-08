<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class StoriesPageSeeder extends AbstractSeed
{
    public function run(): void
    {
        $cols  = $this->fetchAll("SHOW COLUMNS FROM pages");
        $names = array_map(fn($c) => strtolower($c['Field']), $cols);

        if (!in_array('title', $names, true) || !in_array('slug', $names, true)) {
            echo "[SKIP] pages table missing title/slug columns\n";
            return;
        }

        $exists = $this->fetchRow("SELECT 1 AS ok FROM pages WHERE slug = 'stories' LIMIT 1");
        if ($exists) {
            echo "[SKIP] pages.slug=stories already exists\n";
            return;
        }

        $pdo = $this->getAdapter()->getConnection();

        if (in_array('content', $names, true)) {
            $this->execute("
                INSERT INTO pages (title, slug, content)
                VALUES (
                    " . $pdo->quote('Stories in Haarlem') . ",
                    " . $pdo->quote('stories') . ",
                    " . $pdo->quote('CMS page for Stories section.') . "
                )
            ");
        } else {
            $this->execute("
                INSERT INTO pages (title, slug)
                VALUES (
                    " . $pdo->quote('Stories in Haarlem') . ",
                    " . $pdo->quote('stories') . "
                )
            ");
        }

        echo "[DONE] Inserted pages row for stories\n";
    }
}