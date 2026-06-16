<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\MailArchive;
use App\Repositories\OrderRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;
use App\Views\EmailView;
use App\Views\InvoiceView;

final class OrderConfirmationMailer
{
    private OrderRepository $orders;
    private UserRepository $users;
    private SettingsRepository $settings;
    private InvoicePdfService $invoicePdf;
    private TicketPdfService $ticketPdf;
    private MailArchive $archive;
    private SmtpMailer $mail;

    public function __construct()
    {
        $this->orders = new OrderRepository();
        $this->users = new UserRepository();
        $this->settings = new SettingsRepository();
        $this->invoicePdf = new InvoicePdfService();
        $this->ticketPdf = new TicketPdfService();
        $this->archive = new MailArchive();
        $this->mail = new SmtpMailer();
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
        $name = trim($user->firstName . ' ' . $user->lastName);
        $lines = $this->orders->getOrderLineItemsForInvoice($orderId);
        $tickets = $this->orders->getTicketCodesForOrder($orderId);

        $subject = "{$site} — Order #{$orderId} (tickets)";
        $body = EmailView::paidOrder($site, $orderId, $order, $lines, $tickets);

        $invoiceHtml = InvoiceView::html($order, $lines, $name, $user->email, $site);
        $invoicePdf = $this->invoicePdf->render($invoiceHtml);
        $ticketsPdf = $this->ticketPdf->render(
            $this->orders->getTicketsWithDetailsForOrder($orderId),
            $name,
            $site
        );

        $attachments = [
            ['name' => 'invoice-' . $orderId . '.pdf', 'content' => $invoicePdf],
            ['name' => 'tickets-' . $orderId . '.pdf', 'content' => $ticketsPdf],
        ];

        $this->archive->log($user->email, $subject, $body);
        $this->archive->savePdfs($attachments);
        $this->mail->send($user->email, $subject, $body, $attachments);
    }

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
        $subject = "{$site} — Order #{$orderId} reserved (pay within 24h)";
        $body = EmailView::pendingReservation($site, $orderId, $order, $lines);

        $this->archive->log($user->email, $subject, $body);
        $this->mail->send($user->email, $subject, $body);
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
        $subject = "{$site} — Reminder: complete payment for order #{$orderId}";
        $body = EmailView::pendingReminder($site, $orderId, $totalAmount, $expiresAt);

        $this->archive->log($user->email, $subject, $body);
        $this->mail->send($user->email, $subject, $body);
    }

    private function siteName(): string
    {
        return (string) ($this->settings->getAll()['site_name'] ?? 'Haarlem Festival');
    }
}
