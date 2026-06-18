<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\TicketScanServiceInterface;
use App\Contracts\ServiceInterface\SettingsServiceInterface;
use App\Repositories\TicketRepository;

// gate scanner — lookup qr code and mark scanned
final class TicketScanService implements TicketScanServiceInterface
{
    private const MAX_BATCH = 4;
    private const CODE_TAIL_LENGTH = 6;

    public function __construct(
        private TicketRepository $ticketRepository,
        private SettingsServiceInterface $settingsService,
    ) {
    }

    // header/nav settings for admin layout
    public function appSettings(): array
    {
        return $this->settingsService->getAll();
    }

    // single code or batch from textarea — group box wins
    public function scanInput(array $post): array
    {
        $groupRaw = trim($this->postString($post, 'group_codes'));
        if ($groupRaw !== '') {
            return $this->scanBatch($groupRaw);
        }

        $code = $this->normalizeCode($this->postString($post, 'ticket_code'));
        if ($code === '') {
            return ['type' => 'empty'];
        }

        return $this->scanOneCode($code);
    }

    private function scanBatch(string $groupRaw): array
    {
        $codes = $this->parseCodes($groupRaw);
        if ($codes === []) {
            return ['type' => 'empty_batch'];
        }

        $results = [];
        foreach ($codes as $code) {
            $results[] = $this->scanOneCode($code);
        }

        return ['type' => 'batch', 'results' => $results];
    }

    private function parseCodes(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        if ($lines === false) {
            $lines = [];
        }

        $codes = [];
        foreach ($lines as $line) {
            $code = $this->normalizeCode($line);
            if ($code !== '') {
                $codes[] = $code;
            }
        }

        return array_slice(array_values(array_unique($codes)), 0, self::MAX_BATCH);
    }

    private function normalizeCode(string $raw): string
    {
        return strtolower((string) preg_replace('/\s+/', '', $raw));
    }

    // lookup + mark scanned in repo
    private function scanOneCode(string $code): array
    {
        $row = $this->ticketRepository->findByCodeWithDetails($code);
        if ($row === null) {
            return ['type' => 'not_found', 'ticket_code' => $code];
        }

        $outcome = $this->ticketRepository->markScannedIfValid($this->rowInt($row, 'ticket_id'));

        return $this->buildScanResult($code, $outcome, $row);
    }

    private function buildScanResult(string $code, string $outcome, array $row): array
    {
        return [
            'type' => $outcome,
            'ticket_code' => $code,
            'ticket_name' => $this->rowText($row, 'ticket_name'),
            'ticket_type' => $this->rowText($row, 'ticket_type'),
            'event_title' => $this->rowText($row, 'event_title'),
            'event_day' => $this->rowText($row, 'event_day'),
            'code_tail' => $this->codeTail($code),
        ];
    }

    private function codeTail(string $code): string
    {
        if (strlen($code) > self::CODE_TAIL_LENGTH) {
            return substr($code, -self::CODE_TAIL_LENGTH);
        }

        return $code;
    }

    private function postString(array $post, string $key, string $default = ''): string
    {
        return isset($post[$key]) ? (string) $post[$key] : $default;
    }

    private function rowText(array $row, string $key): string
    {
        return isset($row[$key]) ? (string) $row[$key] : '';
    }

    private function rowInt(array $row, string $key): int
    {
        return isset($row[$key]) ? (int) $row[$key] : 0;
    }
}
