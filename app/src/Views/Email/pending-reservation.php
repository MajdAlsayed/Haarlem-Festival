<?php
/** @var string $site */
/** @var int $orderId */
/** @var array{total_amount?:string,expires_at?:string} $order */
/** @var list<array{name:string,quantity:int,unit_price:string,line_total:string}> $lines */

$expires = (string) ($order['expires_at'] ?? '');
$deadline = $expires !== '' ? $expires : '(see your account)';
?>
Your tickets at <?= $site ?> are reserved — payment is still due.

Order #<?= $orderId ?>
Total: €<?= $order['total_amount'] ?? '0' ?>
Complete payment before: <?= $deadline ?>

--- Reserved lines ---
<?php foreach ($lines as $line): ?>
<?= $line['name'] ?> × <?= (string) $line['quantity'] ?> @ €<?= $line['unit_price'] ?> = €<?= $line['line_total'] ?>

<?php endforeach; ?>
Pay from: /account/order/<?= $orderId ?>
