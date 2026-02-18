<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSiteSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('site_settings');
        $table->addColumn('setting_key', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('setting_value', 'text', ['null' => true])
            ->addIndex(['setting_key'], ['unique' => true])
            ->create();
    }
}
