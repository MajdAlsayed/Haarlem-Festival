<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Align site_settings css_version with current assets so browsers fetch fresh style.css (?v=…).
 */
final class BumpCssVersionCache extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            "UPDATE site_settings SET setting_value = '27' WHERE setting_key = 'css_version'"
        );
    }

    public function down(): void
    {
        $this->execute(
            "UPDATE site_settings SET setting_value = '18' WHERE setting_key = 'css_version'"
        );
    }
}
