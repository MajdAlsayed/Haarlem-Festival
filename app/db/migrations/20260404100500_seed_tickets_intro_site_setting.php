<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SeedTicketsIntroSiteSetting extends AbstractMigration
{
    public function change(): void
    {
        $row = $this->fetchRow("SELECT setting_key FROM site_settings WHERE setting_key = 'tickets_intro' LIMIT 1");
        if ($row) {
            return;
        }
        $this->table('site_settings')->insert([
            [
                'setting_key' => 'tickets_intro',
                'setting_value' => 'Explore all events from History tours to Jazz concerts, Dance nights, and Stories performances.',
            ],
        ])->save();
    }
}
