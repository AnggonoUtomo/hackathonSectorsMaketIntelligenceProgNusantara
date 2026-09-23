<?php

namespace App\Modules\Research\Application;

use App\Modules\Company\Application\FakeCompanySnapshot;
use InvalidArgumentException;

class RuleBasedResearchExplainer
{
    public function __construct(private readonly FakeCompanySnapshot $snapshots) {}

    /**
     * @return array{
     *     symbol: string,
     *     company: array{symbol: string, name: string, sector: string, subSector: string, summary: string},
     *     metrics: list<array{label: string, value: string, note: string, explanation: string}>,
     *     bullets: list<string>,
     *     meta: array{source: string, state: string, aiEnabled: bool, disclaimer: string}
     * }
     */
    public function explain(?string $symbol): array
    {
        $symbol = $this->normalizeSymbol($symbol);
        $snapshot = $this->snapshots->find($symbol);

        if ($snapshot === null) {
            throw new InvalidArgumentException("Invalid research symbol [{$symbol}].");
        }

        $isPending = $snapshot['meta']['source'] === 'detail_pending';

        return [
            'symbol' => $snapshot['symbol'],
            'company' => [
                'symbol' => $snapshot['symbol'],
                'name' => $snapshot['name'],
                'sector' => $snapshot['sector'],
                'subSector' => $snapshot['subSector'],
                'summary' => $snapshot['summary'],
            ],
            'metrics' => array_map(fn (array $metric): array => [
                'label' => $metric['label'],
                'value' => $metric['value'],
                'note' => $metric['note'],
                'explanation' => $this->metricExplanation($metric['label'], $metric['value'], $isPending),
            ], $snapshot['metrics']),
            'bullets' => $isPending ? [
                'Ticker valid, tetapi detail dan scoring real belum dimuat.',
                'NusaLens belum menghitung Nilai Prioritas Riset untuk ticker ini.',
                'Gunakan hasil ini sebagai penanda pekerjaan riset berikutnya, bukan sinyal investasi.',
            ] : [
                'Nilai dibaca dari snapshot internal saat ini.',
                'Kelengkapan data membantu menilai seberapa kuat bukti input yang tersedia.',
                'Penjelasan ini berbasis aturan dan tidak memakai AI generatif.',
            ],
            'meta' => [
                'source' => $snapshot['meta']['source'],
                'state' => $isPending ? 'pending' : 'ready',
                'aiEnabled' => false,
                'disclaimer' => 'NusaLens adalah alat informasi dan riset, bukan rekomendasi BUY/HOLD/SELL.',
            ],
        ];
    }

    private function normalizeSymbol(?string $symbol): string
    {
        $symbol = strtoupper(trim((string) ($symbol ?: 'BBCA')));
        $symbol = preg_replace('/\.JK$/i', '', $symbol) ?? '';

        if (! preg_match('/^[A-Z0-9]{2,12}$/', $symbol)) {
            throw new InvalidArgumentException("Invalid research symbol [{$symbol}].");
        }

        return $symbol;
    }

    private function metricExplanation(string $label, string $value, bool $isPending): string
    {
        if ($isPending || $value === '-') {
            return 'Belum ada angka yang dapat dijelaskan karena data detail belum dimuat.';
        }

        return match ($label) {
            'Nilai Prioritas Riset' => 'Angka ini merangkum prioritas riset dari metrik yang tersedia, tanpa label rekomendasi.',
            'Kelengkapan Data' => 'Persentase ini menunjukkan seberapa lengkap input yang mendukung pembacaan nilai.',
            'ROE', 'Revenue Growth YoY', 'Net Income Growth YoY' => 'Metrik ini membantu membaca kualitas dan pertumbuhan bisnis secara awal.',
            'PBV', 'PER' => 'Metrik valuasi perlu dibaca terhadap peer yang sejenis, bukan berdiri sendiri.',
            default => 'Metrik ini menjadi bukti pendukung dalam snapshot riset saat ini.',
        };
    }
}
