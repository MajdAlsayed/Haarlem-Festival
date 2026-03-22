<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Cryptographically secure tokens for tickets / QR (not guessable).
 * Use when inserting into `tickets.ticket_code` or building signed QR payloads.
 */
final class SecureToken
{
    /** 32 hex chars (128-bit) — unique index friendly, URL-safe. */
    public static function ticketCode(): string
    {
        return bin2hex(random_bytes(16));
    }

    /** RFC 4122 style UUID v4 (string) for displays / QR text. */
    public static function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    /**
     * HMAC signature for a ticket code (e.g. second part of QR: code|signature).
     * Set app.php `ticket_signing_secret` or env HAARLEM_TICKET_SECRET in production.
     */
    public static function signTicketCode(string $ticketCode, string $secret): string
    {
        return hash_hmac('sha256', $ticketCode, $secret);
    }

    public static function verifyTicketSignature(string $ticketCode, string $signature, string $secret): bool
    {
        $expected = self::signTicketCode($ticketCode, $secret);

        return hash_equals($expected, $signature);
    }
}
