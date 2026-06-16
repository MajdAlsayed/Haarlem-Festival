<?php
/** @var string $site */
/** @var int $orderId */
/** @var string $totalAmount */
/** @var string $expiresAt */
?>
Reminder: order #<?= $orderId ?> at <?= $site ?> is still unpaid.
Total: €<?= $totalAmount ?>
Payment deadline: <?= $expiresAt ?>

Complete payment: /account/order/<?= $orderId ?>
