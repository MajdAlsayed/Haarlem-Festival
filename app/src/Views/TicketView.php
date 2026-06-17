<?php

declare(strict_types=1);

namespace App\Views;

final class TicketView
{

    public static function html(array $tickets, string $customerName, string $site, array $qrByCode): string
    {
        $siteEsc = self::esc($site);
        $nameEsc = self::esc($customerName);

        $pages = '';
        $last = count($tickets) - 1;
        foreach ($tickets as $index => $ticket) {
            $code = self::rowText($ticket, 'ticket_code');
            $qr = isset($qrByCode[$code]) ? $qrByCode[$code] : '';
            $pages .= self::ticketPage($ticket, $nameEsc, $siteEsc, $qr, $index === $last);
        }

        ob_start();
        require __DIR__ . '/Ticket/document.php';

        return (string) ob_get_clean();
    }

    private static function ticketPage(array $ticket, string $name, string $site, string $qr, bool $isLast): string
    {
        $event = self::esc(self::eventTitle($ticket));
        $item = self::esc(self::rowText($ticket, 'item_name'));
        $type = self::esc(self::rowText($ticket, 'ticket_type'));
        $when = self::formatWhen(
            self::nullableText($ticket, 'event_day'),
            self::nullableText($ticket, 'start_time'),
        );
        $code = self::esc(self::rowText($ticket, 'ticket_code'));
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

    private static function eventTitle(array $ticket): string
    {
        $title = self::rowText($ticket, 'event_title');
        if ($title !== '') {
            return $title;
        }

        return self::rowText($ticket, 'item_name');
    }

    private static function formatWhen(?string $eventDay, ?string $startTime): string
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

        return $when !== '' ? self::esc($when) : 'See programme';
    }

    private static function rowText(array $row, string $key): string
    {
        return isset($row[$key]) ? (string) $row[$key] : '';
    }

    private static function nullableText(array $row, string $key): ?string
    {
        $text = self::rowText($row, $key);

        return $text !== '' ? $text : null;
    }

    private static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
