<?php

namespace App\Modules\Screening\Application;

use Illuminate\Support\Collection;

class FakeDiscoverScreener
{
    /**
     * @param  array{keyword?: string, sector?: string, min_score?: numeric-string|int|float, limit?: numeric-string|int}  $filters
     * @return array{
     *     filters: array{keyword: string, sector: string, minScore: string, limit: string},
     *     results: list<array{symbol: string, name: string, sector: string, score: string, completeness: string, freshness: string}>,
     *     meta: array{source: string, state: string, estimatedCredits: int, cachePolicy: string, liveProvider: bool}
     * }
     */
    public function search(array $filters): array
    {
        $criteria = [
            'keyword' => (string) ($filters['keyword'] ?? ''),
            'sector' => (string) ($filters['sector'] ?? 'all'),
            'minScore' => array_key_exists('min_score', $filters) ? (string) $filters['min_score'] : '',
            'limit' => array_key_exists('limit', $filters) ? (string) $filters['limit'] : '10',
        ];

        $results = $this->companies()
            ->when($criteria['keyword'] !== '', function (Collection $items) use ($criteria): Collection {
                $keyword = mb_strtolower($criteria['keyword']);

                return $items->filter(fn (array $company): bool => str_contains(mb_strtolower($company['symbol'].' '.$company['name']), $keyword));
            })
            ->when($criteria['sector'] !== '' && $criteria['sector'] !== 'all', fn (Collection $items): Collection => $items->where('sectorKey', $criteria['sector']))
            ->when($criteria['minScore'] !== '', fn (Collection $items): Collection => $items->filter(fn (array $company): bool => $company['scoreValue'] >= (float) $criteria['minScore']))
            ->take((int) $criteria['limit'])
            ->map(fn (array $company): array => [
                'symbol' => $company['symbol'],
                'name' => $company['name'],
                'sector' => $company['sector'],
                'score' => $company['score'],
                'completeness' => $company['completeness'],
                'freshness' => $company['freshness'],
            ])
            ->values()
            ->all();

        return [
            'filters' => $criteria,
            'results' => $results,
            'meta' => [
                'source' => 'backend_fake',
                'state' => $results === [] ? 'empty' : 'ready',
                'estimatedCredits' => 1,
                'cachePolicy' => '1 jam',
                'liveProvider' => false,
            ],
        ];
    }

    /**
     * @return Collection<int, array{
     *     symbol: string,
     *     name: string,
     *     sector: string,
     *     sectorKey: string,
     *     score: string,
     *     scoreValue: float,
     *     completeness: string,
     *     freshness: string
     * }>
     */
    private function companies(): Collection
    {
        return collect([
            [
                'symbol' => 'BBCA',
                'name' => 'Bank Central Asia Tbk',
                'sector' => 'Financials',
                'sectorKey' => 'financials',
                'score' => '82,45',
                'scoreValue' => 82.45,
                'completeness' => '91,00',
                'freshness' => 'Cache 42 menit',
            ],
            [
                'symbol' => 'TLKM',
                'name' => 'Telkom Indonesia Tbk',
                'sector' => 'Infrastructure',
                'sectorKey' => 'infrastructure',
                'score' => '78,20',
                'scoreValue' => 78.20,
                'completeness' => '86,50',
                'freshness' => 'Cache 18 menit',
            ],
            [
                'symbol' => 'ICBP',
                'name' => 'Indofood CBP Sukses Makmur Tbk',
                'sector' => 'Consumer Non-Cyclicals',
                'sectorKey' => 'consumer',
                'score' => '74,85',
                'scoreValue' => 74.85,
                'completeness' => '88,00',
                'freshness' => 'Cache 51 menit',
            ],
        ]);
    }
}
