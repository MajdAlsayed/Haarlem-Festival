<?php

declare(strict_types=1);

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Sanitizes admin-authored HTML for jazz artist “career highlights” blocks.
 */
final class JazzArtistHtmlSanitizer
{
    /** Puts HTMLPurifier’s cache somewhere writable so shared hosting doesn’t explode. */
    private static function applyWritableSerializerCache(HTMLPurifier_Config $config): void
    {
        $base = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'haarlem-htmlpurifier';
        if (!is_dir($base) && !@mkdir($base, 0775, true) && !is_dir($base)) {
            $base = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
        }
        if (is_dir($base) && is_writable($base)) {
            $config->set('Cache.SerializerPath', $base);
        }
    }

    /** Strips everything except a small allow-list of tags/classes used on artist pages. */
    public static function purifyHighlights(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $config = HTMLPurifier_Config::createDefault();
        self::applyWritableSerializerCache($config);
        $config->set('HTML.Allowed', 'p[class],br,strong[class],b[class],em,span[class],a[href|target|rel]');
        $config->set('Attr.AllowedClasses', [
            'jazz-gare-accent',
            'jazz-gumbo-accent',
            'jazz-gumbo-year',
            'jazz-artist-accent',
            'jazz-highlight-text',
        ]);
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.TargetNoopener', true);
        $config->set('HTML.TargetNoreferrer', true);

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }
}
