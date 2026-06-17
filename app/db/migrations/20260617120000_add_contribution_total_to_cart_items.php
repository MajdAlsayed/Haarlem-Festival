<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddContributionTotalToCartItems extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('cart_items');

        if (!$table->hasColumn('contribution_total')) {
            $table->addColumn('contribution_total', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'null' => true,
                'after' => 'quantity',
            ])->update();
        }
    }
}
