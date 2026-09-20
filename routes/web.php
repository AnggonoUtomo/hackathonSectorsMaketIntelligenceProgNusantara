<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('temukan-saham', function (Request $request) {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:20'],
            'sector' => ['nullable', 'string', 'in:all,financials,consumer,infrastructure'],
            'min_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $criteria = [
            'keyword' => (string) ($filters['keyword'] ?? ''),
            'sector' => (string) ($filters['sector'] ?? 'all'),
            'minScore' => array_key_exists('min_score', $filters) ? (string) $filters['min_score'] : '',
            'limit' => array_key_exists('limit', $filters) ? (string) $filters['limit'] : '10',
        ];

        $results = collect([
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
        ])
            ->when($criteria['keyword'] !== '', function ($items) use ($criteria) {
                $keyword = mb_strtolower($criteria['keyword']);

                return $items->filter(fn (array $company): bool => str_contains(mb_strtolower($company['symbol'].' '.$company['name']), $keyword));
            })
            ->when($criteria['sector'] !== '' && $criteria['sector'] !== 'all', fn ($items) => $items->where('sectorKey', $criteria['sector']))
            ->when($criteria['minScore'] !== '', fn ($items) => $items->filter(fn (array $company): bool => $company['scoreValue'] >= (float) $criteria['minScore']))
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

        return Inertia::render('nusalens/placeholder', [
            'section' => 'discover',
            'discover' => [
                'filters' => $criteria,
                'results' => $results,
                'meta' => [
                    'source' => 'backend_fake',
                    'state' => $results === [] ? 'empty' : 'ready',
                    'estimatedCredits' => 1,
                    'cachePolicy' => '1 jam',
                    'liveProvider' => false,
                ],
            ],
        ]);
    })->name('discover');

    Route::get('perusahaan', function () {
        return Inertia::render('nusalens/placeholder', ['section' => 'companies']);
    })->name('companies');

    Route::get('bandingkan', function () {
        return Inertia::render('nusalens/placeholder', ['section' => 'compare']);
    })->name('compare');

    Route::get('jelaskan-nilai', function () {
        return Inertia::render('nusalens/placeholder', ['section' => 'research']);
    })->name('research');

    Route::get('kandidat-menarik', function () {
        return Inertia::render('nusalens/placeholder', ['section' => 'candidates']);
    })->name('candidates');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
