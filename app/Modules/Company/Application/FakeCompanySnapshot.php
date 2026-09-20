<?php

namespace App\Modules\Company\Application;

class FakeCompanySnapshot
{
    /**
     * @return null|array{
     *     symbol: string,
     *     name: string,
     *     sector: string,
     *     subSector: string,
     *     summary: string,
     *     metrics: list<array{label: string, value: string, note: string}>,
     *     meta: array{source: string, freshness: string, liveProvider: bool}
     * }
     */
    public function find(string $symbol): ?array
    {
        $symbol = strtoupper($symbol);

        return $this->snapshots()[$symbol] ?? null;
    }

    /**
     * @return array<string, array{
     *     symbol: string,
     *     name: string,
     *     sector: string,
     *     subSector: string,
     *     summary: string,
     *     metrics: list<array{label: string, value: string, note: string}>,
     *     meta: array{source: string, freshness: string, liveProvider: bool}
     * }>
     */
    private function snapshots(): array
    {
        return [
            'BBCA' => [
                'symbol' => 'BBCA',
                'name' => 'Bank Central Asia Tbk',
                'sector' => 'Financials',
                'subSector' => 'Banks',
                'summary' => 'Contoh snapshot bank berkapitalisasi besar untuk mempelajari tampilan detail, freshness, dan bukti metrik.',
                'metrics' => [
                    ['label' => 'Nilai Prioritas Riset', 'value' => '82,45', 'note' => 'Contoh hasil scoring internal'],
                    ['label' => 'Kelengkapan Data', 'value' => '91,00%', 'note' => 'Minimal peer dan input terpenuhi'],
                    ['label' => 'ROE', 'value' => '21,12%', 'note' => 'Placeholder query_values'],
                    ['label' => 'PBV', 'value' => '4,72x', 'note' => 'Tidak dipakai sebagai rekomendasi'],
                ],
                'meta' => [
                    'source' => 'backend_fake',
                    'freshness' => 'Cache 42 menit',
                    'liveProvider' => false,
                ],
            ],
            'TLKM' => [
                'symbol' => 'TLKM',
                'name' => 'Telkom Indonesia Tbk',
                'sector' => 'Infrastructure',
                'subSector' => 'Telecommunication',
                'summary' => 'Contoh snapshot perusahaan non-bank untuk memastikan layout mendukung sektor selain perbankan.',
                'metrics' => [
                    ['label' => 'Nilai Prioritas Riset', 'value' => '78,20', 'note' => 'Contoh hasil scoring internal'],
                    ['label' => 'Kelengkapan Data', 'value' => '86,50%', 'note' => 'Input utama tersedia'],
                    ['label' => 'Revenue Growth YoY', 'value' => '7,84%', 'note' => 'Placeholder query_values'],
                    ['label' => 'PBV', 'value' => '2,41x', 'note' => 'Tidak dipakai sebagai rekomendasi'],
                ],
                'meta' => [
                    'source' => 'backend_fake',
                    'freshness' => 'Cache 18 menit',
                    'liveProvider' => false,
                ],
            ],
            'ICBP' => [
                'symbol' => 'ICBP',
                'name' => 'Indofood CBP Sukses Makmur Tbk',
                'sector' => 'Consumer Non-Cyclicals',
                'subSector' => 'Processed Foods',
                'summary' => 'Contoh snapshot consumer goods untuk membandingkan metrik non-bank dan kelengkapan data.',
                'metrics' => [
                    ['label' => 'Nilai Prioritas Riset', 'value' => '74,85', 'note' => 'Contoh hasil scoring internal'],
                    ['label' => 'Kelengkapan Data', 'value' => '88,00%', 'note' => 'Input utama tersedia'],
                    ['label' => 'Net Income Growth YoY', 'value' => '9,35%', 'note' => 'Placeholder query_values'],
                    ['label' => 'PER', 'value' => '15,64x', 'note' => 'Tidak dipakai sebagai rekomendasi'],
                ],
                'meta' => [
                    'source' => 'backend_fake',
                    'freshness' => 'Cache 51 menit',
                    'liveProvider' => false,
                ],
            ],
        ];
    }
}
