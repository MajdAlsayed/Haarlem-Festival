<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Rename photos → site_photos and prefix context with dance_ so names are clear
 * (e.g. dance_event_detail, dance_artist_schedule) and don't conflict with other sections.
 */
final class RenamePhotosToSitePhotosAndContexts extends AbstractMigration
{
    public function change(): void
    {
        if (!$this->hasTable('photos')) {
            return;
        }
        $this->execute('RENAME TABLE photos TO site_photos');
        $this->execute("UPDATE site_photos SET context = CONCAT('dance_', context) WHERE context IN ('event_detail', 'artist_schedule', 'artist_music')");
    }
}
