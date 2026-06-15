<?php

declare(strict_types=1);

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;
use Dompdf\Dompdf;
use Dompdf\Options;

// renders the order's tickets as a pdf, one ticket per page, each with a scannable qr
final class TicketPdfService
{
    /**
     * @param list<array{ticket_code:string,item_name:string,ticket_type:string,event_title:?string,event_day:?string,start_time:?string}> $tickets
     */
    public function render(array $tickets, string $customerName, string $site): string
    {
        return $this->toPdf($this->buildHtml($tickets, $customerName, $site));
    }

    /**
     * @param list<array{ticket_code:string,item_name:string,ticket_type:string,event_title:?string,event_day:?string,start_time:?string}> $tickets
     */
    private function buildHtml(array $tickets, string $customerName, string $site): string
    {
        $site = $this->esc($site);
        $name = $this->esc($customerName);

        $pages = '';
        $last = count($tickets) - 1;
        foreach ($tickets as $i => $ticket) {
            $pages .= $this->ticketPage($ticket, $name, $site, $i === $last);
        }

        return <<<HTML
        <!DOCTYPE html>
        <html><head><meta charset="utf-8"><style>
            body { font-family: DejaVu Sans, sans-serif; color: #222; }
            .ticket { text-align: center; padding: 40px 20px; }
            .brand { color: #c8862b; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
            h1 { font-size: 24px; margin: 8px 0 18px; }
            table.meta { margin: 0 auto 18px; font-size: 13px; }
            table.meta td { padding: 4px 10px; text-align: left; }
            table.meta .label { color: #888; text-transform: uppercase; font-size: 11px; }
            .qr { margin: 10px 0; }
            .code { font-family: DejaVu Sans Mono, monospace; font-size: 12px; color: #444; }
            .hint { color: #888; font-size: 11px; margin-top: 8px; }
        </style></head><body>{$pages}</body></html>
        HTML;
    }

    /**
     * @param array{ticket_code:string,item_name:string,ticket_type:string,event_title:?string,event_day:?string,start_time:?string} $ticket
     */
    private function ticketPage(array $ticket, string $name, string $site, bool $isLast): string
    {
        // prefer the event title, fall back to the ticket name
        $event = $ticket['event_title'];
        if ($event === null || $event === '') {
            $event = $ticket['item_name'];
        }
        $event = $this->esc($event);

        $item = $this->esc($ticket['item_name']);
        $type = $this->esc($ticket['ticket_type']);
        $when = $this->formatWhen($ticket['event_day'], $ticket['start_time']);
        $code = $this->esc($ticket['ticket_code']);
        $qr = $this->qrDataUri($ticket['ticket_code']);
        // every ticket starts on its own page, except the last one
        $break = $isLast ? '' : ' style="page-break-after: always;"';

        return <<<HTML
        <div class="ticket"{$break}>
            <div class="brand">{$site}</div>
            <h1>{$event}</h1>
            <table class="meta">
                <tr><td class="label">Ticket</td><td>{$item}</td></tr>
                <tr><td class="label">Type</td><td>{$type}</td></tr>
                <tr><td class="label">When</td><td>{$when}</td></tr>
                <tr><td class="label">Name</td><td>{$name}</td></tr>
            </table>
            <div class="qr"><img src="{$qr}" width="200" height="200"></div>
            <div class="code">{$code}</div>
            <div class="hint">Show this QR code at the entrance.</div>
        </div>
        HTML;
    }

    private function formatWhen(?string $eventDay, ?string $startTime): string
    {
        $day = '';
        if ($eventDay !== null && $eventDay !== '') {
            $day = ucfirst($eventDay);
        }

        $time = '';
        if ($startTime !== null && $startTime !== '') {
            $time = substr($startTime, 0, 5);
        }

        $when = trim($day . ' ' . $time);

        return $when !== '' ? $this->esc($when) : 'See programme';
    }

    // a scannable qr of the ticket code, as a png data uri dompdf can embed
    private function qrDataUri(string $code): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => true,
            'scale' => 5,
        ]);

        return (new QRCode($options))->render($code);
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

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
