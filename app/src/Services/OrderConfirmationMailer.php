<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;

/** Paid confirmation (with PDF invoice + tickets), pay-later reservation, and payment reminders. */
final class OrderConfirmationMailer
{
    private OrderRepository $orders;
    private UserRepository $users;
    private SettingsRepository $settings;
    private InvoicePdfService $invoicePdf;
    private TicketPdfService $ticketPdf;

    // deps can be injected (for tests), otherwise we build the defaults
    public function __construct(
        ?OrderRepository $orders = null,
        ?UserRepository $users = null,
        ?SettingsRepository $settings = null,
        ?InvoicePdfService $invoicePdf = null,
        ?TicketPdfService $ticketPdf = null
    ) {
        $this->orders = $orders ?? new OrderRepository();
        $this->users = $users ?? new UserRepository();
        $this->settings = $settings ?? new SettingsRepository();
        $this->invoicePdf = $invoicePdf ?? new InvoicePdfService();
        $this->ticketPdf = $ticketPdf ?? new TicketPdfService();
    }

    public function send(int $orderId, int $userId): void
    {
        $user = $this->users->findById($userId);
        if ($user === null) {
            return;
        }

        $order = $this->orders->findForCustomer($orderId, $userId);
        if ($order === null || ($order['status'] ?? '') !== 'paid') {
            return;
        }

        $site = $this->siteName();
        $customerName = trim($user->firstName . ' ' . $user->lastName);
        $lines = $this->orders->getOrderLineItemsForInvoice($orderId);
        $tickets = $this->orders->getTicketCodesForOrder($orderId);

        $subject = "{$site} — Order #{$orderId} (tickets)";
        $plain = $this->buildPlainBody($site, $orderId, $order, $lines, $tickets);

        // generate the pdf invoice + tickets and attach them to the email
        $invoicePdf = $this->invoicePdf->render($order, $lines, $customerName, $user->email, $site);
        $ticketRows = $this->orders->getTicketsWithDetailsForOrder($orderId);
        $ticketsPdf = $this->ticketPdf->render($ticketRows, $customerName, $site);

        $attachments = [
            'invoice-' . $orderId . '.pdf' => $invoicePdf,
            'tickets-' . $orderId . '.pdf' => $ticketsPdf,
        ];

        $this->savePdfs($attachments);
        $this->appendLog($user->email, $subject, $plain . "\n\n[attached: invoice + tickets PDF]");
        $this->sendWithAttachments($user->email, $subject, $plain, $attachments);
    }

    /** After "Pay later" reserve: cart held as pending order (no ticket codes yet). */
    public function sendPendingReservation(int $orderId, int $userId): void
    {
        $user = $this->users->findById($userId);
        if ($user === null) {
            return;
        }

        $order = $this->orders->findForCustomer($orderId, $userId);
        if ($order === null || ($order['status'] ?? '') !== 'pending') {
            return;
        }

        $site = $this->siteName();
        $lines = $this->orders->getOrderLineItemsForInvoice($orderId);
        $expires = (string) ($order['expires_at'] ?? '');

        $buf = [];
        $buf[] = "Your tickets at {$site} are reserved — payment is still due.";
        $buf[] = '';
        $buf[] = "Order #{$orderId}";
        $buf[] = 'Total: €' . ($order['total_amount'] ?? '0');
        $buf[] = 'Complete payment before: ' . ($expires !== '' ? $expires : '(see your account)');
        $buf[] = '';
        $buf[] = '--- Reserved lines ---';
        foreach ($lines as $l) {
            $buf[] = sprintf('%s × %s @ €%s = €%s', $l['name'], (string) $l['quantity'], $l['unit_price'], $l['line_total']);
        }
        $buf[] = '';
        $buf[] = 'Pay from: /account/order/' . $orderId;

        $subject = "{$site} — Order #{$orderId} reserved (pay within 24h)";
        $plain = implode("\n", $buf);
        $this->appendLog($user->email, $subject, $plain);
        $this->tryPhpMail($user->email, $subject, $plain);
    }

    public function sendPendingPaymentReminder(int $orderId, int $userId, string $totalAmount, string $expiresAt): void
    {
        $user = $this->users->findById($userId);
        if ($user === null) {
            return;
        }

        $order = $this->orders->findForCustomer($orderId, $userId);
        if ($order === null || ($order['status'] ?? '') !== 'pending') {
            return;
        }

        $site = $this->siteName();
        $plain = "Reminder: order #{$orderId} at {$site} is still unpaid.\n"
            . "Total: €{$totalAmount}\n"
            . "Payment deadline: {$expiresAt}\n\n"
            . 'Complete payment: /account/order/' . $orderId;

        $subject = "{$site} — Reminder: complete payment for order #{$orderId}";
        $this->appendLog($user->email, $subject, $plain);
        $this->tryPhpMail($user->email, $subject, $plain);
    }

    private function siteName(): string
    {
        return (string) ($this->settings->getAll()['site_name'] ?? 'Haarlem Festival');
    }

    /**
     * @param array{order_id?:int,status?:string,total_amount?:string,paid_at?:string,created_at?:string} $order
     * @param list<array{name:string,quantity:int,unit_price:string,line_total:string}> $lines
     * @param list<array{ticket_code:string,item_name:string}> $tickets
     */
    private function buildPlainBody(string $site, int $orderId, array $order, array $lines, array $tickets): string
    {
        $buf = [];
        $buf[] = "Thank you for your order at {$site}.";
        $buf[] = '';
        $buf[] = "Order #{$orderId}";
        $buf[] = 'Status: ' . ($order['status'] ?? '');
        $buf[] = 'Total: €' . ($order['total_amount'] ?? '0');
        $buf[] = 'Paid at: ' . ($order['paid_at'] ?? '—');
        $buf[] = '';
        $buf[] = '--- Invoice lines ---';
        foreach ($lines as $l) {
            $buf[] = sprintf('%s × %s @ €%s = €%s', $l['name'], (string) $l['quantity'], $l['unit_price'], $l['line_total']);
        }
        $buf[] = '';
        $buf[] = '--- Your ticket codes (show at entrance) ---';
        foreach ($tickets as $t) {
            $buf[] = ($t['item_name'] ?? '') . ': ' . ($t['ticket_code'] ?? '');
        }
        $buf[] = '';
        $buf[] = 'View orders anytime: /account/orders';

        return implode("\n", $buf);
    }

    /** Keep a copy of the generated PDFs so they can be opened/verified locally. */
    private function savePdfs(array $attachments): void
    {
        $base = dirname(__DIR__, 2) . '/storage/mail';
        if (!is_dir($base) && !@mkdir($base, 0755, true) && !is_dir($base)) {
            return;
        }
        foreach ($attachments as $filename => $bytes) {
            @file_put_contents($base . '/' . $filename, $bytes);
        }
    }

    /** Plain-text email with the PDFs attached (multipart/mixed). */
    private function sendWithAttachments(string $to, string $subject, string $body, array $attachments): void
    {
        $from = getenv('MAIL_FROM') ?: 'noreply@haarlem-festival.local';
        $boundary = 'hf_' . bin2hex(random_bytes(8));
        $nl = "\r\n";

        $headers = 'From: ' . $from . $nl
            . 'MIME-Version: 1.0' . $nl
            . 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

        $msg = '--' . $boundary . $nl
            . 'Content-Type: text/plain; charset=UTF-8' . $nl . $nl
            . $body . $nl . $nl;

        foreach ($attachments as $filename => $bytes) {
            $msg .= '--' . $boundary . $nl
                . 'Content-Type: application/pdf; name="' . $filename . '"' . $nl
                . 'Content-Transfer-Encoding: base64' . $nl
                . 'Content-Disposition: attachment; filename="' . $filename . '"' . $nl . $nl
                . chunk_split(base64_encode($bytes)) . $nl;
        }
        $msg .= '--' . $boundary . '--';

        @mail($to, $subject, $msg, $headers);
    }

    /** Demo-friendly: tail app/storage/mail/orders.log; swap for real SMTP in production. */
    private function appendLog(string $toEmail, string $subject, string $body): void
    {
        $base = dirname(__DIR__, 2) . '/storage/mail';
        if (!is_dir($base) && !@mkdir($base, 0755, true) && !is_dir($base)) {
            return;
        }

        $line = str_repeat('=', 60) . "\n"
            . date('c') . "\n"
            . 'To: ' . $toEmail . "\n"
            . 'Subject: ' . $subject . "\n\n"
            . $body . "\n\n";
        @file_put_contents($base . '/orders.log', $line, FILE_APPEND | LOCK_EX);
    }

    private function tryPhpMail(string $to, string $subject, string $body): void
    {
        $from = getenv('MAIL_FROM') ?: 'noreply@haarlem-festival.local';
        $headers = 'From: ' . $from . "\r\nContent-Type: text/plain; charset=UTF-8";
        @mail($to, $subject, $body, $headers);
    }
}
