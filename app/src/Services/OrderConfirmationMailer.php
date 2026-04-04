<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;

/**
 * Sends order confirmation + ticket codes. Always appends to storage/mail/orders.log (Docker-friendly).
 * Also attempts PHP mail() when the server is configured for it.
 */
final class OrderConfirmationMailer
{
    public function send(int $orderId, int $userId): void
    {
        $user = (new UserRepository())->findById($userId);
        if ($user === null) {
            return;
        }

        $orders = new OrderRepository();
        $order = $orders->findForCustomer($orderId, $userId);
        if ($order === null || ($order['status'] ?? '') !== 'paid') {
            return;
        }

        $lines = $orders->getOrderLineItemsForInvoice($orderId);
        $tickets = $orders->getTicketCodesForOrder($orderId);
        $app = (new SettingsRepository())->getAll();
        $site = (string) ($app['site_name'] ?? 'Haarlem Festival');

        $plain = $this->buildPlainBody($site, $orderId, $order, $lines, $tickets);
        $subject = "{$site} — Order #{$orderId} (tickets)";

        $this->appendLog($user->email, $subject, $plain);
        $this->tryPhpMail($user->email, $subject, $plain);
    }

    /** After “Pay later” reserve: cart held as pending order (no ticket codes yet). */
    public function sendPendingReservation(int $orderId, int $userId): void
    {
        $user = (new UserRepository())->findById($userId);
        if ($user === null) {
            return;
        }

        $orders = new OrderRepository();
        $order = $orders->findForCustomer($orderId, $userId);
        if ($order === null || ($order['status'] ?? '') !== 'pending') {
            return;
        }

        $lines = $orders->getOrderLineItemsForInvoice($orderId);
        $app = (new SettingsRepository())->getAll();
        $site = (string) ($app['site_name'] ?? 'Haarlem Festival');
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

        $plain = implode("\n", $buf);
        $subject = "{$site} — Order #{$orderId} reserved (pay within 24h)";
        $this->appendLog($user->email, $subject, $plain);
        $this->tryPhpMail($user->email, $subject, $plain);
    }

    public function sendPendingPaymentReminder(int $orderId, int $userId, string $totalAmount, string $expiresAt): void
    {
        $user = (new UserRepository())->findById($userId);
        if ($user === null) {
            return;
        }

        $orders = new OrderRepository();
        $order = $orders->findForCustomer($orderId, $userId);
        if ($order === null || ($order['status'] ?? '') !== 'pending') {
            return;
        }

        $app = (new SettingsRepository())->getAll();
        $site = (string) ($app['site_name'] ?? 'Haarlem Festival');

        $plain = "Reminder: order #{$orderId} at {$site} is still unpaid.\n"
            . "Total: €{$totalAmount}\n"
            . "Payment deadline: {$expiresAt}\n\n"
            . 'Complete payment: /account/order/' . $orderId;

        $subject = "{$site} — Reminder: complete payment for order #{$orderId}";
        $this->appendLog($user->email, $subject, $plain);
        $this->tryPhpMail($user->email, $subject, $plain);
    }

    /**
     * @param array{order_id?:int,status?:string,total_amount?:string,paid_at?:string,created_at?:string} $order
     * @param list<array{name:string,quantity:int,unit_price:string,line_total:string}> $lines
     * @param list<array{ticket_code:string,item_name:string}> $tickets
     */
    private function buildPlainBody(
        string $site,
        int $orderId,
        array $order,
        array $lines,
        array $tickets
    ): string {
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
            $buf[] = sprintf(
                '%s × %s @ €%s = €%s',
                $l['name'],
                (string) $l['quantity'],
                $l['unit_price'],
                $l['line_total']
            );
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
