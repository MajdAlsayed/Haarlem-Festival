<?php

declare(strict_types=1);

namespace App\Core;

// writes mail copies to storage/mail for local dev / demo verification
final class MailArchive
{
    private string $dir;

    public function __construct()
    {
        $this->dir = dirname(__DIR__, 2) . '/storage/mail';
    }

    public function log(string $toEmail, string $subject, string $body): void
    {
        if (!$this->ensureDir()) {
            return;
        }

        $line = str_repeat('=', 60) . "\n"
            . date('c') . "\n"
            . 'To: ' . $toEmail . "\n"
            . 'Subject: ' . $subject . "\n\n"
            . $body . "\n\n";
        @file_put_contents($this->dir . '/orders.log', $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * @param list<array{name:string,content:string}> $pdfAttachments
     */
    public function savePdfs(array $pdfAttachments): void
    {
        if (!$this->ensureDir()) {
            return;
        }

        foreach ($pdfAttachments as $pdf) {
            @file_put_contents($this->dir . '/' . $pdf['name'], $pdf['content']);
        }
    }

    private function ensureDir(): bool
    {
        if (is_dir($this->dir)) {
            return true;
        }

        return @mkdir($this->dir, 0755, true) || is_dir($this->dir);
    }
}
