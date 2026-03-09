<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateJazzInfoPath extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("UPDATE event_types SET info_path='/jazz' WHERE LOWER(name)='jazz'");
    }

    public function down(): void
    {
        $this->execute("UPDATE event_types SET info_path='#' WHERE LOWER(name)='jazz'");
    }
}