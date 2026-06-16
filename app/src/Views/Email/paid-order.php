<?php
/** @var string $site */
/** @var int $orderId */
/** @var array{status?:string,total_amount?:string,paid_at?:string} $order */
/** @var list<array{name:string,quantity:int,unit_price:string,line_total:string}> $lines */
/** @var list<array{ticket_code:string,item_name:string}> $tickets */
?>
Thank you for your order at <?= $site ?>.

Order #<?= $orderId ?>
Status: <?= $order['status'] ?? '' ?>

Total: €<?= $order['total_amount'] ?? '0' ?>
Paid at: <?= $order['paid_at'] ?? '—' ?>

--- Invoice lines ---
<?php foreach ($lines as $line): ?>
<?= $line['name'] ?> × <?= (string) $line['quantity'] ?> @ €<?= $line['unit_price'] ?> = €<?= $line['line_total'] ?>

<?php endforeach; ?>
--- Your ticket codes (show at entrance) ---
<?php foreach ($tickets as $ticket): ?>
<?= $ticket['item_name'] ?? '' ?>: <?= $ticket['ticket_code'] ?? '' ?>

<?php endforeach; ?>
View orders anytime: /account/orders
