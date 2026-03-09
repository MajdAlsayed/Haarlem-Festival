<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateJazzSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('jazz_settings', [
            'id' => false,
            'primary_key' => ['setting_key'],
        ]);

        $table
            ->addColumn('setting_key', 'string', ['limit' => 100])
            ->addColumn('setting_value', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_LONG])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->create();
    }
}