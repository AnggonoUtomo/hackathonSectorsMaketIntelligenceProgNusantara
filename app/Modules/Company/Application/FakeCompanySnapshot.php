<?php

namespace App\Modules\Company\Application;

class FakeCompanySnapshot
{
    /**
     * @return list<array{symbol: string, name: string, sector: string, subSector: string, freshness: string, source: string}>
     */
    public function list(): array
    {
        return array_values(array_map(
            fn (array $snapshot): array => [
                'symbol' => $snapshot['symbol'],
                'name' => $snapshot['name'],
                'sector' => $snapshot['sector'],
                'subSector' => $snapshot['subSector'],
                'freshness' => $snapshot['meta']['freshness'],
                'source' => $snapshot['meta']['source'],
            ],
            $this->snapshots(),
        ));
    }

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
        $symbol = strtoupper(preg_replace('/\.JK$/i', '', $symbol) ?? '');

        if (! preg_match('/^[A-Z0-9]{4,8}$/', $symbol)) {
            return null;
        }

        return $this->snapshots()[$symbol] ?? $this->pendingSnapshot($symbol);
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

    /**
     * @return array{
     *     symbol: string,
     *     name: string,
     *     sector: string,
     *     subSector: string,
     *     summary: string,
     *     metrics: list<array{label: string, value: string, note: string}>,
     *     meta: array{source: string, freshness: string, liveProvider: bool}
     * }
     */
    private function pendingSnapshot(string $symbol): array
    {
        return [
            'symbol' => $symbol,
            'name' => $symbol.' - data detail belum dimuat',
            'sector' => 'Belum tersedia',
            'subSector' => 'Belum tersedia',
            'summary' => 'Ticker ini berasal dari hasil screener. Detail perusahaan real belum diambil agar credit Sectors tetap hemat.',
            'metrics' => [
                ['label' => 'Nilai Prioritas Riset', 'value' => '-', 'note' => 'Menunggu scoring real'],
                ['label' => 'Kelengkapan Data', 'value' => '-', 'note' => 'Menunggu Company Report'],
                ['label' => 'Sektor', 'value' => '-', 'note' => 'Belum dimuat pada snapshot detail'],
                ['label' => 'Status', 'value' => 'Pending', 'note' => 'Siap dihubungkan ke data real'],
            ],
            'meta' => [
                'source' => 'detail_pending',
                'freshness' => 'Belum diambil',
                'liveProvider' => false,
            ],
        ];
    }
}
