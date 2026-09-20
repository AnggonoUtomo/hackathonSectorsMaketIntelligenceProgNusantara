<?php

use App\Modules\Company\Application\FakeCompanySnapshot;
use App\Modules\Screening\Application\FakeDiscoverScreener;
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

    Route::get('temukan-saham', function (Request $request, FakeDiscoverScreener $screener) {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:20'],
            'sector' => ['nullable', 'string', 'in:all,financials,consumer,infrastructure'],
            'min_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

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
