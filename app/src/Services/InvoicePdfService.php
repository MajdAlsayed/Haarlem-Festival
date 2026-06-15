<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

// renders the order invoice as a pdf (prices are vat-inclusive, dutch 9% cultural rate)
final class InvoicePdfService
{
    // festival tickets use the 9% dutch reduced (cultural) vat rate
    private const VAT_RATE = 0.09;

    /**
     * @param array<string, mixed> $order
     * @param list<array{name:string,quantity:int,unit_price:string,line_total:string}> $lines
     */
    public function render(array $order, array $lines, string $customerName, string $customerEmail, string $site): string
    {
        return $this->toPdf($this->buildHtml($order, $lines, $customerName, $customerEmail, $site));
    }

    /**
     * @param array<string, mixed> $order
     * @param list<array{name:string,quantity:int,unit_price:string,line_total:string}> $lines
     */
    private function buildHtml(array $order, array $lines, string $customerName, string $customerEmail, string $site): string
    {
        $orderId = (int) ($order['order_id'] ?? 0);
        $invoiceNo = $this->esc('INV-' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT));

        // show the paid date, or fall back to the created date
        $date = (string) ($order['paid_at'] ?? '');
        if ($date === '') {
            $date = (string) ($order['created_at'] ?? '');
        }
        $date = $this->esc($date);

        // prices include vat, so split the total into net + vat
        $total = (float) ($order['total_amount'] ?? 0);
        $net = $this->money($total / (1 + self::VAT_RATE));
        $vat = $this->money($total - ($total / (1 + self::VAT_RATE)));
        $totalStr = $this->money($total);

        $site = $this->esc($site);
        $name = $this->esc($customerName);
        $email = $this->esc($customerEmail);
        $rows = $this->lineRows($lines);

        return <<<HTML
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
                    <h1>{$site}</h1>
                    <div class="muted">Gedempte Voldersgracht 24<br>2011 WD Haarlem, Netherlands<br>VAT: NL000000000B01</div>
                </td>
                <td style="text-align:right">
                    <h1>Invoice</h1>
                    <div><strong>{$invoiceNo}</strong></div>
                    <div class="muted">Date: {$date}</div>
                </td>
            </tr></table>

            <p><strong>Bill to:</strong><br>{$name}<br>{$email}</p>

            <table class="items">
                <thead><tr><th>Item</th><th class="num">Qty</th><th class="num">Unit</th><th class="num">Total</th></tr></thead>
                <tbody>{$rows}</tbody>
            </table>

            <table class="totals">
                <tr><td>Subtotal (excl. VAT)</td><td class="num">&euro; {$net}</td></tr>
                <tr><td>VAT 9%</td><td class="num">&euro; {$vat}</td></tr>
                <tr class="grand"><td>Total</td><td class="num">&euro; {$totalStr}</td></tr>
            </table>

            <p class="muted" style="margin-top:30px">Thank you for your order. This invoice was generated automatically.</p>
        </body></html>
        HTML;
    }

    /**
     * @param list<array{name:string,quantity:int,unit_price:string,line_total:string}> $lines
     */
    private function lineRows(array $lines): string
    {
        $rows = '';
        foreach ($lines as $line) {
            $name = $this->esc($line['name']);
            $qty = $this->esc((string) $line['quantity']);
            $unit = $this->esc($line['unit_price']);
            $lineTotal = $this->esc($line['line_total']);
            $rows .= "<tr><td>{$name}</td><td class=\"num\">{$qty}</td><td class=\"num\">&euro; {$unit}</td><td class=\"num\">&euro; {$lineTotal}</td></tr>";
        }

        return $rows;
    }

    private function toPdf(string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    private function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
