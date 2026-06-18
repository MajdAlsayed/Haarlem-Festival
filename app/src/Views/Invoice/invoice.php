<?php

$h = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
    body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 12px; }
    h1 { font-size: 22px; margin: 0 0 4px; }
    .muted { color: #666; }
    .row { width: 100%; }
    .row td { vertical-align: top; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 18px; }
    table.items th, table.items td { border-bottom: 1px solid #ddd; padding: 7px 6px; text-align: left; }
    table.items th { background: #f4f4f4; }
    .num { text-align: right; }
    table.totals { width: 45%; margin-left: 55%; margin-top: 14px; }
    table.totals td { padding: 4px 6px; }
    table.totals .grand td { border-top: 2px solid #333; font-weight: bold; font-size: 14px; }
</style></head><body>
    <table class="row"><tr>
        <td>
            <h1><?= $h($site) ?></h1>
            <div class="muted">Gedempte Voldersgracht 24<br>2011 WD Haarlem, Netherlands<br>VAT: NL000000000B01</div>
        </td>
        <td style="text-align:right">
            <h1>Invoice</h1>
            <div><strong><?= $h($invoiceNo) ?></strong></div>
            <div class="muted">Date: <?= $h($date) ?></div>
        </td>
    </tr></table>

    <p><strong>Bill to:</strong><br><?= $h($customerName) ?><br><?= $h($customerEmail) ?></p>

    <table class="items">
        <thead><tr><th>Item</th><th class="num">Qty</th><th class="num">Unit</th><th class="num">Total</th></tr></thead>
        <tbody>
            <?php foreach ($lines as $line): ?>
            <tr>
                <td><?= $h($line['name']) ?></td>
                <td class="num"><?= $h((string) $line['quantity']) ?></td>
                <td class="num">&euro; <?= $h($line['unit_price']) ?></td>
                <td class="num">&euro; <?= $h($line['line_total']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal (excl. VAT)</td><td class="num">&euro; <?= $h($net) ?></td></tr>
        <tr><td>VAT 9%</td><td class="num">&euro; <?= $h($vat) ?></td></tr>
        <tr class="grand"><td>Total</td><td class="num">&euro; <?= $h($totalStr) ?></td></tr>
    </table>

    <p class="muted" style="margin-top:30px">Thank you for your order. This invoice was generated automatically.</p>
</body></html>
