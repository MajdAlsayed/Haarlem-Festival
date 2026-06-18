<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

// order emails via phpmailer env vars
final class SmtpMailer
{
    // skip send when env vars missing
    public function isConfigured(): bool
    {
        return $this->env('MAIL_HOST') !== '' && $this->env('MAIL_FROM_ADDRESS') !== '';
    }

    // optional pdf attachments after checkout
    public function send(string $toEmail, string $subject, string $textBody, array $pdfAttachments = []): void
    {
        if (!$this->isConfigured()) {
            return;
        }

        $mail = new PHPMailer(true);

        try {
            $this->configure($mail);
            $mail->setFrom($this->env('MAIL_FROM_ADDRESS'), $this->env('MAIL_FROM_NAME') ?: 'Haarlem Festival');
            $mail->addAddress($toEmail);
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $textBody;

            foreach ($pdfAttachments as $pdf) {
                $mail->addStringAttachment($pdf['content'], $pdf['name'], PHPMailer::ENCODING_BASE64, 'application/pdf');
            }

            $mail->send();
        } catch (PHPMailerException $e) {
            error_log('SmtpMailer failed: ' . $mail->ErrorInfo);
        }
    }

    private function configure(PHPMailer $mail): void
    {
        $mail->isSMTP();
        $mail->Host = $this->env('MAIL_HOST');
        $mail->Port = (int) ($this->env('MAIL_PORT') ?: '587');
        $mail->CharSet = 'UTF-8';

        $username = $this->env('MAIL_USERNAME');
        if ($username !== '') {
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $this->env('MAIL_PASSWORD');
        }

        $encryption = strtolower($this->env('MAIL_ENCRYPTION'));
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }
    }

    private function env(string $key): string
    {
        $value = getenv($key);
        if ($value === false) {
            $value = $_ENV[$key] ?? '';
        }

        return trim((string) $value);
    }
}
