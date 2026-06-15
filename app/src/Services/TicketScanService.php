<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\TicketScanServiceInterface;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketRepository;

// the scanning logic for the door scanner (/admin/scan)
class TicketScanService implements TicketScanServiceInterface
{
    // staff can paste a few codes at once, but we only scan the first 4
    private const MAX_BATCH = 4;

    public function __construct(
        private TicketRepository $ticketRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    // global site settings the scanner page layout needs
    public function appSettings(): array
    {
        return $this->settingsRepository->getAll();
    }

    // work out what was submitted (one code or a batch) and return the scan result
    /** @return array<string, mixed> */
    public function scanInput(array $post): array
    {
        // the group box wins on purpose: batch mode is explicit, so it takes priority
        $groupRaw = trim((string) ($post['group_codes'] ?? ''));
        if ($groupRaw !== '') {
            return $this->scanBatch($groupRaw);
        }

        $code = $this->normalizeCode((string) ($post['ticket_code'] ?? ''));
        if ($code === '') {
            return ['type' => 'empty'];
        }

        return $this->scanOneCode($code);
    }

    // scan up to MAX_BATCH codes pasted into the group box
    /** @return array<string, mixed> */
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

    // one code per line, cleaned up, de-duplicated, capped at MAX_BATCH
    /** @return string[] */
    private function parseCodes(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $codes = [];
        foreach ($lines as $line) {
            $code = $this->normalizeCode($line);
            if ($code !== '') {
                $codes[] = $code;
            }
        }

        return array_slice(array_values(array_unique($codes)), 0, self::MAX_BATCH);
    }

    // codes are lowercase with no spaces
    private function normalizeCode(string $raw): string
    {
        return strtolower((string) preg_replace('/\s+/', '', $raw));
    }

    // look a code up and mark it scanned if it's valid
    /** @return array<string, mixed> */
    private function scanOneCode(string $code): array
    {
        // findByCodeWithDetails only returns tickets tied to a paid order
        $row = $this->ticketRepository->findByCodeWithDetails($code);
        if ($row === null) {
            return ['type' => 'not_found', 'ticket_code' => $code];
        }

        $outcome = $this->ticketRepository->markScannedIfValid((int) $row['ticket_id']);

        return [
            'type' => $outcome,
            'ticket_code' => $code,
            'ticket_name' => (string) ($row['ticket_name'] ?? ''),
            'ticket_type' => (string) ($row['ticket_type'] ?? ''),
            'event_title' => $row['event_title'] !== null ? (string) $row['event_title'] : '',
            'event_day' => $row['event_day'] !== null ? (string) $row['event_day'] : '',
            'code_tail' => strlen($code) > 6 ? substr($code, -6) : $code,
        ];
    }
}
