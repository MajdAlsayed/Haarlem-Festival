<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\MailArchive;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;
use App\Views\EmailView;
use App\Views\InvoiceView;
use App\Views\TicketView;

// checkout emails — paid confirmation, reservation, pay-later reminders
final class OrderConfirmationMailer
{
    private const DEFAULT_SITE_NAME = 'Haarlem Festival';
    private const STATUS_PAID = 'paid';
    private const STATUS_PENDING = 'pending';

    public function __construct(
        private OrderRepository $orderRepository = new OrderRepository(),
        private UserRepository $userRepository = new UserRepository(),
        private SettingsRepository $settingsRepository = new SettingsRepository(),
        private InvoicePdfService $invoicePdfService = new InvoicePdfService(),
        private TicketPdfService $ticketPdfService = new TicketPdfService(),
        private QrCodeService $qrCodeService = new QrCodeService(),
        private MailArchive $mailArchive = new MailArchive(),
        private SmtpMailer $smtpMailer = new SmtpMailer(),
    ) {
    }

    // paid order: email + invoice + tickets pdfs
    public function send(int $orderId, int $userId): void
    {
        $context = $this->customerOrder($orderId, $userId, self::STATUS_PAID);
        if ($context === null) {
            return;
        }

        [$user, $order] = $context;
        $site = $this->siteName();
        $name = $this->customerName($user);
        $lines = $this->orderRepository->getOrderLineItemsForInvoice($orderId);
        $tickets = $this->orderRepository->getTicketCodesForOrder($orderId);

        $subject = "{$site} — Order #{$orderId} (tickets)";
        $body = EmailView::paidOrder($site, $orderId, $order, $lines, $tickets);
        $attachments = $this->buildPaidOrderAttachments($orderId, $order, $lines, $name, $user->email, $site);

        $this->deliverEmail($user->email, $subject, $body, $attachments);
    }

    /**
     * @param array<string, mixed> $order
     * @param list<array<string, mixed>> $lines
     * @return list<array{name: string, content: string}>
     */
    private function buildPaidOrderAttachments(
        int $orderId,
        array $order,
        array $lines,
        string $name,
        string $email,
        string $site,
    ): array {
        $attachments = [];

        try {
            $attachments[] = [
                'name' => 'invoice-' . $orderId . '.pdf',
                'content' => $this->invoicePdfService->render(
                    InvoiceView::html($order, $lines, $name, $email, $site),
                ),
            ];
        } catch (\Throwable $e) {
            error_log('OrderConfirmationMailer: invoice PDF skipped for order #' . $orderId . ': ' . $e->getMessage());
        }

        try {
            $attachments[] = [
                'name' => 'tickets-' . $orderId . '.pdf',
                'content' => $this->buildTicketsPdf($orderId, $name, $site),
            ];
        } catch (\Throwable $e) {
            error_log('OrderConfirmationMailer: tickets PDF skipped for order #' . $orderId . ': ' . $e->getMessage());
        }

        return $attachments;
    }

    // pay-later: email only, no pdfs yet
    public function sendPendingReservation(int $orderId, int $userId): void
    {
        $context = $this->customerOrder($orderId, $userId, self::STATUS_PENDING);
        if ($context === null) {
            return;
        }

        [$user, $order] = $context;
        $site = $this->siteName();
        $lines = $this->orderRepository->getOrderLineItemsForInvoice($orderId);
        $subject = "{$site} — Order #{$orderId} reserved (pay within 24h)";
        $body = EmailView::pendingReservation($site, $orderId, $order, $lines);

        $this->deliverEmail($user->email, $subject, $body);
    }

    // cron-style nudge before pending order expires
    public function sendPendingPaymentReminder(int $orderId, int $userId, string $totalAmount, string $expiresAt): void
    {
        $context = $this->customerOrder($orderId, $userId, self::STATUS_PENDING);
        if ($context === null) {
            return;
        }

        [$user] = $context;
        $site = $this->siteName();
        $subject = "{$site} — Reminder: complete payment for order #{$orderId}";
        $body = EmailView::pendingReminder($site, $orderId, $totalAmount, $expiresAt);

        $this->deliverEmail($user->email, $subject, $body);
    }

    private function customerOrder(int $orderId, int $userId, string $status): ?array
    {
        $user = $this->userRepository->findById($userId);
        if ($user === null) {
            return null;
        }

        $order = $this->orderRepository->findForCustomer($orderId, $userId);
        if ($order === null || $this->rowText($order, 'status') !== $status) {
            return null;
        }

        return [$user, $order];
    }

    private function deliverEmail(string $email, string $subject, string $body, array $attachments = []): void
    {
        $this->mailArchive->log($email, $subject, $body);
        if ($attachments !== []) {
            $this->mailArchive->savePdfs($attachments);
        }

        $this->smtpMailer->send($email, $subject, $body, $attachments);
    }

    private function buildTicketsPdf(int $orderId, string $customerName, string $site): string
    {
        $tickets = $this->orderRepository->getTicketsWithDetailsForOrder($orderId);
        $qrByCode = $this->qrCodeService->pngDataUrisForCodes($this->ticketCodes($tickets));
        $html = TicketView::html($tickets, $customerName, $site, $qrByCode);

        return $this->ticketPdfService->render($html);
    }

    private function ticketCodes(array $tickets): array
    {
        $codes = [];
        foreach ($tickets as $ticket) {
            $codes[] = $this->rowText($ticket, 'ticket_code');
        }

        return $codes;
    }

    private function customerName(User $user): string
    {
        return trim($user->firstName . ' ' . $user->lastName);
    }

    private function siteName(): string
    {
        $name = $this->settingText($this->settingsRepository->getAll(), 'site_name');

        return $name !== '' ? $name : self::DEFAULT_SITE_NAME;
    }

    private function settingText(array $settings, string $key): string
    {
        if (isset($settings[$key]) && is_string($settings[$key])) {
            return $settings[$key];
        }

        return '';
    }

    private function rowText(array $row, string $key): string
    {
        return isset($row[$key]) ? (string) $row[$key] : '';
    }
}
