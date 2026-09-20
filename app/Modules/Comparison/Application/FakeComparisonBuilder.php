<?php

namespace App\Modules\Comparison\Application;

use InvalidArgumentException;

class FakeComparisonBuilder
{
    private const LIMIT = 3;

    /**
     * @return array{
     *     symbols: list<string>,
     *     companies: list<array{symbol: string, name: string, sector: string, freshness: string}>,
     *     metrics: list<array{label: string, values: array<string, string>, notes: array<string, string>}>,
     *     meta: array{source: string, state: string, limit: int, liveProvider: bool}
     * }
     */
    public function build(?string $symbols): array
    {
        $requested = $this->parseSymbols($symbols);
        $dataset = $this->companies();

        foreach ($requested as $symbol) {
            if (! array_key_exists($symbol, $dataset)) {
                throw new InvalidArgumentException("Unknown comparison symbol [{$symbol}].");
            }
        }

        $companies = array_map(
            fn (string $symbol): array => [
                'symbol' => $symbol,
                'name' => $dataset[$symbol]['name'],
                'sector' => $dataset[$symbol]['sector'],
                'freshness' => $dataset[$symbol]['freshness'],
            ],
            $requested,
        );

        return [
            'symbols' => $requested,
            'companies' => $companies,
            'metrics' => $this->metrics($requested, $dataset),
            'meta' => [
                'source' => 'backend_fake',
                'state' => $requested === [] ? 'empty' : 'ready',
                'limit' => self::LIMIT,
                'liveProvider' => false,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function parseSymbols(?string $symbols): array
    {
        if ($symbols === null || trim($symbols) === '') {
            return [];
        }

        $parsed = array_values(array_unique(array_filter(array_map(
            fn (string $symbol): string => strtoupper(trim($symbol)),
            explode(',', $symbols),
        ))));

        foreach ($parsed as $symbol) {
            if (! preg_match('/^[A-Z0-9]{2,12}$/', $symbol)) {
                throw new InvalidArgumentException("Invalid comparison symbol [{$symbol}].");
            }
        }

        if (count($parsed) > self::LIMIT) {
            throw new InvalidArgumentException('Comparison supports up to 3 symbols.');
        }

        return $parsed;
    }

    /**
     * @param  list<string>  $symbols
     * @param  array<string, array<string, string>>  $dataset
     * @return list<array{label: string, values: array<string, string>, notes: array<string, string>}>
     */
    private function metrics(array $symbols, array $dataset): array
    {
        $metricKeys = [
            'researchScore' => 'Nilai Prioritas Riset',
            'completeness' => 'Kelengkapan Data',
            'quality' => 'Kesehatan Bisnis',
            'valuation' => 'Harga Saham',
            'risk' => 'Keamanan Keuangan',
        ];

        return array_map(function (string $metricKey, string $label) use ($symbols, $dataset): array {
            $values = [];
            $notes = [];

            foreach ($symbols as $symbol) {
                $values[$symbol] = $dataset[$symbol][$metricKey];
                $notes[$symbol] = $dataset[$symbol][$metricKey.'Note'];
            }

            return [
                'label' => $label,
                'values' => $values,
                'notes' => $notes,
            ];
        }, array_keys($metricKeys), array_values($metricKeys));
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function companies(): array
    {
        return [
            'BBCA' => [
                'name' => 'Bank Central Asia Tbk',
                'sector' => 'Financials',
                'freshness' => 'Cache 42 menit',
                'researchScore' => '82,45',
                'researchScoreNote' => 'Contoh skor tertinggi pada dataset fake',
                'completeness' => '91,00%',
                'completenessNote' => 'Input fake paling lengkap',
                'quality' => '84,20',
                'qualityNote' => 'ROE placeholder kuat',
                'valuation' => '68,40',
                'valuationNote' => 'PBV tinggi perlu konteks peer bank',
                'risk' => '88,10',
                'riskNote' => 'Profil fake defensif',
            ],
            'TLKM' => [
                'name' => 'Telkom Indonesia Tbk',
                'sector' => 'Infrastructure',
                'freshness' => 'Cache 18 menit',
                'researchScore' => '78,20',
                'researchScoreNote' => 'Contoh perusahaan non-bank',
                'completeness' => '86,50%',
                'completenessNote' => 'Input fake cukup lengkap',
                'quality' => '77,35',
                'qualityNote' => 'Margin dan arus kas placeholder stabil',
                'valuation' => '73,15',
                'valuationNote' => 'Valuasi placeholder lebih moderat',
                'risk' => '80,00',
                'riskNote' => 'Leverage placeholder terkendali',
            ],
            'ICBP' => [
                'name' => 'Indofood CBP Sukses Makmur Tbk',
                'sector' => 'Consumer Non-Cyclicals',
                'freshness' => 'Cache 51 menit',
                'researchScore' => '74,85',
                'researchScoreNote' => 'Contoh consumer goods',
                'completeness' => '88,00%',
                'completenessNote' => 'Input fake utama tersedia',
                'quality' => '79,10',
                'qualityNote' => 'Profitability placeholder baik',
                'valuation' => '70,25',
                'valuationNote' => 'PER placeholder moderat',
                'risk' => '76,75',
                'riskNote' => 'Risiko fake menengah',
            ],
        ];
    }
}
