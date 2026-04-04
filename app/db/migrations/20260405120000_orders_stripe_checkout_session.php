<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OrdersStripeCheckoutSession extends AbstractMigration
{
    public function change(): void
    {
        $this->table('orders')
            ->addColumn('stripe_checkout_session_id', 'string', ['limit' => 255, 'null' => true])
            ->addIndex(['stripe_checkout_session_id'], ['unique' => true, 'name' => 'orders_stripe_session_uidx'])
            ->update();
    }
}
