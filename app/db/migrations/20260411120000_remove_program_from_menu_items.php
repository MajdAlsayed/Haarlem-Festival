<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** Public /program nav item removed; personal list stays at /my-program (account menu). */
final class RemoveProgramFromMenuItems extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("DELETE FROM menu_items WHERE path = '/program'");
    }
}
