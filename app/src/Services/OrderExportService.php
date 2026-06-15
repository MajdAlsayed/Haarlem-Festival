<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\OrderExportServiceInterface;
use App\Exceptions\ValidationException;
use App\Repositories\OrderRepository;

/**
 * Builds a CSV or Excel order export from the admin form (format + chosen columns).
 */
final class OrderExportService implements OrderExportServiceInterface
{
    /** Whitelist: export row key => column title */
    private const COLUMN_LABELS = [
        'order_id' => 'Order ID',
        'user_id' => 'User ID',
        'customer_email' => 'Customer email',
        'customer_first_name' => 'First name',
        'customer_last_name' => 'Last name',
        'status' => 'Status',
        'total_amount' => 'Total amount',
        'paid_at' => 'Paid at',
        'created_at' => 'Created at',
        'line_items_count' => 'Line items',
    ];

    public function __construct(
        private OrderRepository $orderRepository
    ) {
    }

    /** The columns the form offers (key => label). */
    /** @return array<string, string> */
    public function columnLabels(): array
    {
        return self::COLUMN_LABELS;
    }

    /**
     * Validate the form and build the download (body + mime + filename).
     *
     * @param array<string, mixed> $post
     * @return array{body: string, mime: string, filename: string}
     */
    public function buildExport(array $post): array
    {
        $format = strtolower(trim((string) ($post['format'] ?? 'csv')));
        if (!in_array($format, ['csv', 'excel'], true)) {
            throw new ValidationException('Choose a valid export format.');
        }

        $columnKeys = $this->selectedColumns($post['columns'] ?? null);
        $rows = $this->orderRepository->getAllForExport();
        $date = gmdate('Y-m-d');

        if ($format === 'csv') {
            return [
                'body' => $this->toCsvString($rows, $columnKeys, self::COLUMN_LABELS),
                'mime' => 'text/csv; charset=UTF-8',
                'filename' => 'haarlem-orders-' . $date . '.csv',
            ];
        }

        return [
            'body' => $this->toExcelHtmlTable($rows, $columnKeys, self::COLUMN_LABELS),
            'mime' => 'application/vnd.ms-excel; charset=UTF-8',
            'filename' => 'haarlem-orders-' . $date . '.xls',
        ];
    }

    // keep only whitelisted columns (in their listed order); none picked = export everything
    /** @return list<string> */
    private function selectedColumns(mixed $selected): array
    {
        $allowed = array_keys(self::COLUMN_LABELS);
        if (!is_array($selected)) {
            return $allowed;
        }

        $keys = [];
        foreach ($allowed as $key) {
            if (in_array($key, $selected, true)) {
                $keys[] = $key;
            }
        }

        return $keys === [] ? $allowed : $keys;
    }

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
        // escape '' = standard RFC CSV (and avoids the PHP 8.4 fputcsv deprecation)
        fputcsv($fh, $headerLine, ',', '"', '');

        foreach ($rows as $row) {
            $line = [];
            foreach ($columnKeys as $key) {
                $line[] = $this->stringifyCell($row[$key] ?? null);
            }
            fputcsv($fh, $line, ',', '"', '');
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
