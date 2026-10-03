<?php

namespace App\Modules\Comparison\Application;

use App\Modules\Company\Application\Contracts\FactAttestation;
use App\Modules\Comparison\Application\Contracts\ComparisonSnapshotStore;
use App\Modules\Intelligence\Application\Contracts\ScoreEvidence;

class SaveComparisonSnapshot
{
    public function __construct(private readonly ComparisonSnapshotStore $snapshots,
        private readonly FactAttestation $attestation,
        private readonly ScoreEvidence $evidence) {}

    /**
     * @param  list<string>  $symbols
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function execute(string $userId, ?string $title, array $symbols, array $payload, ?string $baseSnapshotId): array
    {
        $symbols = array_values(array_unique(array_map(
            fn (string $symbol): string => preg_replace('/\.JK$/i', '', strtoupper(trim($symbol))) ?? '',
            $symbols,
        )));
        $title = trim((string) $title);
        if ($baseSnapshotId !== null && $this->snapshots->findForUser($userId, $baseSnapshotId) === null) {
            throw new \InvalidArgumentException('Versi sebelumnya tidak ditemukan.');
        }
        $trusted = ['companies' => [], 'sections' => [], 'scores' => [], 'evidence' => [], 'integrity' => 'server-v1'];
        foreach ($symbols as $symbol) {
            $card = null;
            foreach ($payload['companies'] ?? [] as $candidate) {
                if (($candidate['symbol'] ?? null) === $symbol) {
                    $card = $candidate;
                    break;
                }
            }
            $trusted['companies'][] = $this->attestation->open((string) ($card['receipt'] ?? ''), 'profile', $symbol);
            foreach (['prices', 'financials', 'valuation'] as $section) {
                if (isset($payload['sections'][$section][$symbol])) {
                    $trusted['sections'][$section][$symbol] = $this->attestation->open(
                        (string) ($payload['sections'][$section][$symbol]['receipt'] ?? ''), $section, $symbol);
                }
            }
            if (isset($payload['scores'][$symbol])) {
                $record = $this->evidence->find((string) $payload['scores'][$symbol]);
                if ($record === null || $record['result']['symbol'] !== $symbol) {
                    throw new \InvalidArgumentException('Bukti nilai tidak cocok.');
                }
                $trusted['scores'][$symbol] = $record['result'];
                $trusted['evidence'][$symbol] = $record;
            }
        }
        if ($title === '') {
            $title = 'Perbandingan '.implode(', ', $symbols);
        }

        return $this->snapshots->create($userId, $title, $symbols, $trusted, $baseSnapshotId);
    }
}
