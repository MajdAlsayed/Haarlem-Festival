<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

// sends email over smtp (phpmailer), configured from the MAIL_* env vars
final class SmtpMailer
{
    // we only try real smtp when a host + from address are set
    public function isConfigured(): bool
    {
        return $this->env('MAIL_HOST') !== '' && $this->env('MAIL_FROM_ADDRESS') !== '';
    }

    /**
     * @param list<array{name:string,content:string}> $pdfAttachments
     */
    public function sendWithPdfs(string $toEmail, string $subject, string $textBody, array $pdfAttachments): void
    {
        $mail = new PHPMailer(true);
        try {
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
                // no encryption — for a local catcher like mailpit/mailhog
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $fromName = $this->env('MAIL_FROM_NAME') ?: 'Haarlem Festival';
            $mail->setFrom($this->env('MAIL_FROM_ADDRESS'), $fromName);
            $mail->addAddress($toEmail);
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $textBody;

            foreach ($pdfAttachments as $pdf) {
                $mail->addStringAttachment($pdf['content'], $pdf['name'], PHPMailer::ENCODING_BASE64, 'application/pdf');
            }

            $mail->send();
        } catch (PHPMailerException $e) {
            // a failed email must not break the order — the pdfs are still saved + downloadable
            error_log('SmtpMailer failed: ' . $mail->ErrorInfo);
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
