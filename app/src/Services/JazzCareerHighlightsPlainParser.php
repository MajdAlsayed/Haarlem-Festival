<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Turns admin plain text into safe HTML for career highlights.
 *
 * Rules:
 * - One or more blank lines = new paragraph.
 * - Single line breaks inside a paragraph become line breaks on the page.
 * - Wrap text in [[accent]]...[[/accent]] for orange emphasis (class depends on artist slug).
 */
final class JazzCareerHighlightsPlainParser
{
    /** Which CSS accent class [[accent]] wraps should use for this artist. */
    public static function accentClassForSlug(string $artistSlug): string
    {
        return match ($artistSlug) {
            'gare-du-nord' => 'jazz-gare-accent',
            'gumbo-kings' => 'jazz-gumbo-year',
            default => 'jazz-artist-accent',
        };
    }

    public static function toHtml(string $plain, string $artistSlug): string
    {
        $plain = str_replace("\r\n", "\n", $plain);
        $plain = trim($plain);
        if ($plain === '') {
            return '';
        }

        $accentClass = self::accentClassForSlug($artistSlug);
        $paras = preg_split('/\n\s*\n/', $plain) ?: [];
        $html = '';
        foreach ($paras as $para) {
            $para = trim($para);
            if ($para === '') {
                continue;
            }
            $html .= '<p class="jazz-highlight-text">' . self::formatParagraph($para, $accentClass) . '</p>';
        }

        return $html;
    }

    /** Inner loop: escapes text, handles [[accent]]…[[/accent]] spans. */
    private static function formatParagraph(string $para, string $accentClass): string
    {
        $classEsc = htmlspecialchars($accentClass, ENT_QUOTES, 'UTF-8');
        $out = '';
        $offset = 0;
        $len = strlen($para);
        $openTag = '[[accent]]';
        $closeTag = '[[/accent]]';
        $openLen = strlen($openTag);
        $closeLen = strlen($closeTag);

        while ($offset < $len) {
            $open = strpos($para, $openTag, $offset);
            if ($open === false) {
                $out .= self::escapeNl2br(substr($para, $offset));

                break;
            }
            $before = substr($para, $offset, $open - $offset);
            $out .= self::escapeNl2br($before);
            $close = strpos($para, $closeTag, $open + $openLen);
            if ($close === false) {
                $out .= self::escapeNl2br(substr($para, $open));

                break;
            }
            $inner = substr($para, $open + $openLen, $close - $open - $openLen);
            $out .= '<strong class="' . $classEsc . '">' . self::escapeNl2br($inner) . '</strong>';
            $offset = $close + $closeLen;
        }

        return $out;
    }

    /** htmlspecialchars + nl2br for plain stretches. */
    private static function escapeNl2br(string $s): string
    {
        return nl2br(htmlspecialchars($s, ENT_QUOTES, 'UTF-8'), false);
    }
}
