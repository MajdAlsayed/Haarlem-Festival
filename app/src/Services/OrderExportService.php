<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\OrderExportServiceInterface;
use App\Contracts\ServiceInterface\SettingsServiceInterface;
use App\Exceptions\ValidationException;
use App\Repositories\OrderRepository;

// admin orders export — csv or excel from form post
final class OrderExportService implements OrderExportServiceInterface
{
    private const FORMAT_CSV = 'csv';
    private const FORMAT_EXCEL = 'excel';

    private const FILENAME_PREFIX = 'haarlem-orders-';
    private const UTF8_BOM = "\xEF\xBB\xBF";
    private const CSV_DELIMITER = ',';
    private const CSV_ENCLOSURE = '"';
    private const CSV_ESCAPE = '';

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
        private OrderRepository $orderRepository,
        private SettingsServiceInterface $settingsService,
    ) {
    }

    // header/nav settings for admin layout
    public function appSettings(): array
    {
        return $this->settingsService->getAll();
    }

    // column picker form
    public function getExportPageData(): array
    {
        return [
            'app' => $this->appSettings(),
            'columns' => self::COLUMN_LABELS,
        ];
    }

    // keys => labels for the export checkboxes
    public function columnLabels(): array
    {
        return self::COLUMN_LABELS;
    }

    // validate post, return csv or excel bytes
    public function buildExport(array $post): array
    {
        $format = $this->normalizeFormat($this->postString($post, 'format', self::FORMAT_CSV));
        $columnKeys = $this->selectedColumnKeys($post);
        $rows = $this->orderRepository->getAllForExport();
        $basename = self::FILENAME_PREFIX . gmdate('Y-m-d');

        if ($format === self::FORMAT_CSV) {
            return $this->buildDownload(
                $this->toCsvString($rows, $columnKeys),
                'text/csv; charset=UTF-8',
                $basename . '.csv',
            );
        }

        return $this->buildDownload(
            $this->toExcelHtmlTable($rows, $columnKeys),
            'application/vnd.ms-excel; charset=UTF-8',
            $basename . '.xls',
        );
    }

    private function toCsvString(array $rows, array $columnKeys): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new ValidationException('Could not build the export file.');
        }

        fwrite($handle, self::UTF8_BOM);
        fputcsv($handle, $this->headerCells($columnKeys), self::CSV_DELIMITER, self::CSV_ENCLOSURE, self::CSV_ESCAPE);

        foreach ($rows as $row) {
            fputcsv($handle, $this->dataCells($row, $columnKeys), self::CSV_DELIMITER, self::CSV_ENCLOSURE, self::CSV_ESCAPE);
        }

        rewind($handle);
        $body = stream_get_contents($handle);
        fclose($handle);

        if ($body === false) {
            throw new ValidationException('Could not build the export file.');
        }

        return $body;
    }

    private function toExcelHtmlTable(array $rows, array $columnKeys): string
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="UTF-8"></head><body><table border="1">';
        $html .= '<tr>';

        foreach ($columnKeys as $key) {
            $html .= '<th>' . $this->escapeHtml($this->columnLabel($key)) . '</th>';
        }

        $html .= '</tr>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($columnKeys as $key) {
                $html .= '<td>' . $this->escapeHtml($this->stringifyCell($this->rowCell($row, $key))) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        return $html;
    }

    private function selectedColumnKeys(array $post): array
    {
        $allowed = array_keys(self::COLUMN_LABELS);
        $selected = $this->postColumnSelection($post);

        if ($selected === []) {
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

    private function normalizeFormat(string $format): string
    {
        $format = strtolower(trim($format));
        if ($format !== self::FORMAT_CSV && $format !== self::FORMAT_EXCEL) {
            throw new ValidationException('Choose a valid export format.');
        }

        return $format;
    }

    private function headerCells(array $columnKeys): array
    {
        $cells = [];
        foreach ($columnKeys as $key) {
            $cells[] = $this->columnLabel($key);
        }

        return $cells;
    }

    private function dataCells(array $row, array $columnKeys): array
    {
        $cells = [];
        foreach ($columnKeys as $key) {
            $cells[] = $this->stringifyCell($this->rowCell($row, $key));
        }

        return $cells;
    }

    private function buildDownload(string $body, string $mime, string $filename): array
    {
        return [
            'body' => $body,
            'mime' => $mime,
            'filename' => $filename,
        ];
    }

    private function columnLabel(string $key): string
    {
        if (isset(self::COLUMN_LABELS[$key])) {
            return self::COLUMN_LABELS[$key];
        }

        return $key;
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

    private function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function postColumnSelection(array $post): array
    {
        if (!isset($post['columns']) || !is_array($post['columns'])) {
            return [];
        }

        $keys = [];
        foreach ($post['columns'] as $value) {
            if (is_string($value) && $value !== '') {
                $keys[] = $value;
            }
        }

        return $keys;
    }

    private function postString(array $post, string $key, string $default = ''): string
    {
        return isset($post[$key]) ? (string) $post[$key] : $default;
    }

    private function rowCell(array $row, string $key): mixed
    {
        return array_key_exists($key, $row) ? $row[$key] : null;
    }
}
