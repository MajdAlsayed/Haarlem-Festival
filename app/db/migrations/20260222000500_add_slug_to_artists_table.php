<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Adds slug column to existing artists table (created by 20260216000005).
 * Unique class name to avoid conflict with CreateArtistsTable.
 */
final class AddSlugToArtistsTable extends AbstractMigration
{
    public function change(): void
    {
        $adapter = $this->getAdapter();
        $row = $adapter->fetchRow(
            "SELECT 1 FROM information_schema.COLUMNS 
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'artists' AND COLUMN_NAME = 'slug'"
        );
        if (!$row) {
            $this->execute("ALTER TABLE artists ADD COLUMN slug VARCHAR(140) NULL");
            $this->execute("CREATE UNIQUE INDEX uniq_artist_slug ON artists (slug)");
        }
    }
}
