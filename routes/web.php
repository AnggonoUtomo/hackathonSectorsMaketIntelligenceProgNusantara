<?php

use App\Modules\Company\Application\FakeCompanySnapshot;
use App\Modules\Comparison\Application\FakeComparisonBuilder;
use App\Modules\MarketData\Application\DTO\StructuredScreenerCriteria;
use App\Modules\MarketData\Infrastructure\Sectors\StructuredCompanyScreener;
use App\Modules\Screening\Application\FakeDiscoverScreener;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('temukan-saham', function (Request $request, FakeDiscoverScreener $screener, StructuredCompanyScreener $realScreener) {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:20'],
            'sector' => ['nullable', 'string', 'in:all,financials,consumer,infrastructure'],
            'min_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        if (config('marketdata.provider_mode') === 'real') {
            $criteriaFilters = [];

            if (($filters['sector'] ?? 'all') !== 'all') {
                $criteriaFilters[] = [
                    'field' => 'sector',
                    'operator' => 'eq',
                    'value' => match ($filters['sector']) {
                        'financials' => 'Financials',
                        'consumer' => 'Consumer Non-Cyclicals',
                        'infrastructure' => 'Infrastructure',
                        default => $filters['sector'],
                    },
                ];
            }

            if (($filters['keyword'] ?? '') !== '') {
                $criteriaFilters[] = [
                    'field' => 'symbol',
                    'operator' => 'eq',
                    'value' => strtoupper((string) $filters['keyword']),
                ];
            }

            $criteria = StructuredScreenerCriteria::fromFilters(
                filters: $criteriaFilters,
                page: 1,
                perPage: (int) ($filters['limit'] ?? 10),
            );
            $result = $realScreener->screen(
                userId: (string) $request->user()->id,
                criteria: $criteria,
                correlationId: 'discover-'.$request->user()->id.'-'.sha1(json_encode($filters, JSON_THROW_ON_ERROR)),
            );

            return Inertia::render('nusalens/placeholder', [
                'section' => 'discover',
                'discover' => [
                    'filters' => [
                        'keyword' => (string) ($filters['keyword'] ?? ''),
                        'sector' => (string) ($filters['sector'] ?? 'all'),
                        'minScore' => array_key_exists('min_score', $filters) ? (string) $filters['min_score'] : '',
                        'limit' => array_key_exists('limit', $filters) ? (string) $filters['limit'] : '10',
                    ],
                    'results' => array_map(fn ($company): array => [
                        'symbol' => $company->symbol,
                        'name' => $company->name,
                        'sector' => $company->sector ?? '-',
                        'score' => '-',
                        'completeness' => '-',
                        'freshness' => 'Sectors/cache',
                    ], $result->companies),
                    'meta' => [
                        'source' => 'sectors_real',
                        'state' => $result->companies === [] ? 'empty' : 'ready',
                        'estimatedCredits' => 1,
                        'cachePolicy' => '1 jam',
                        'liveProvider' => true,
                    ],
                ],
            ]);
        }

        return Inertia::render('nusalens/placeholder', [
            'section' => 'discover',
            'discover' => $screener->search($filters),
        ]);
    })->name('discover');

    Route::get('perusahaan', function () {
        return Inertia::render('nusalens/placeholder', ['section' => 'companies']);
    })->name('companies');

    Route::get('perusahaan/{symbol}', function (string $symbol, FakeCompanySnapshot $snapshots) {
        $company = $snapshots->find($symbol);

        abort_if($company === null, 404);

        return Inertia::render('nusalens/placeholder', [
            'section' => 'companies',
            'company' => $company,
        ]);
    })->whereAlphaNumeric('symbol')->name('companies.show');

    Route::get('bandingkan', function (Request $request, FakeComparisonBuilder $comparison) {
        $validated = $request->validate([
            'symbols' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $payload = $comparison->build($validated['symbols'] ?? null);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'symbols' => $exception->getMessage(),
            ]);
        }

        return Inertia::render('nusalens/placeholder', [
            'section' => 'compare',
            'comparison' => $payload,
        ]);
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
