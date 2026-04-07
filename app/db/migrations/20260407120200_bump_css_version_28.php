<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BumpCssVersion28 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            "UPDATE site_settings SET setting_value = '28' WHERE setting_key = 'css_version'"
        );
    }

    public function down(): void
    {
        $this->execute(
            "UPDATE site_settings SET setting_value = '27' WHERE setting_key = 'css_version'"
        );
    }
}
