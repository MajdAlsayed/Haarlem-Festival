<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Builds CSV or Excel-friendly export from order rows.
 * Column keys must come from the whitelist in AdminOrderExportController.
 */
final class OrderExportService
{
    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string> $columnKeys keys in display order
     * @param array<string, string> $headers human labels per key
     */
    public function toCsvString(array $rows, array $columnKeys, array $headers): string
    {
        $fh = fopen('php://temp', 'r+');
        if ($fh === false) {
            return '';
        }
        // UTF-8 BOM for Excel
        fwrite($fh, "\xEF\xBB\xBF");

        $headerLine = [];
        foreach ($columnKeys as $key) {
            $headerLine[] = $headers[$key] ?? $key;
        }
        fputcsv($fh, $headerLine);

        foreach ($rows as $row) {
            $line = [];
            foreach ($columnKeys as $key) {
                $line[] = $this->stringifyCell($row[$key] ?? null);
            }
            fputcsv($fh, $line);
        }

        rewind($fh);
        $out = stream_get_contents($fh);
        fclose($fh);

        return $out !== false ? $out : '';
    }

    /**
     * HTML table Excel opens as spreadsheet (.xls).
     *
     * @param list<array<string, mixed>> $rows
     * @param list<string> $columnKeys
     * @param array<string, string> $headers
     */
    public function toExcelHtmlTable(array $rows, array $columnKeys, array $headers): string
    {
        $esc = static function (string $s): string {
            return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        };

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8"></head><body><table border="1">';
        $html .= '<tr>';
        foreach ($columnKeys as $key) {
            $html .= '<th>' . $esc($headers[$key] ?? $key) . '</th>';
        }
        $html .= '</tr>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($columnKeys as $key) {
                $html .= '<td>' . $esc($this->stringifyCell($row[$key] ?? null)) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        return $html;
    }

    private function stringifyCell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_float($value) || is_int($value)) {
            return (string) $value;
        }

        return trim((string) $value);
    }
}
