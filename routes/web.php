<?php

use App\Modules\Company\Presentation\CompanyAnalyticsController;
use App\Modules\Company\Presentation\CompanyProfileController;
use App\Modules\Comparison\Application\ComparisonSelectionBuilder;
use App\Modules\Comparison\Presentation\ComparisonSnapshotController;
use App\Modules\Intelligence\Presentation\ResearchPriorityController;
use App\Modules\Research\Presentation\ResearchSummaryController;
use App\Modules\Screening\Presentation\CompanySearchController;
use App\Modules\Screening\Presentation\PeerCandidatesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

Route::get('/', function (Request $request) {
    return redirect()->route($request->user() ? 'dashboard' : 'login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('temukan-saham', [CompanySearchController::class, 'index'])->middleware('throttle:60,1')->name('discover');
    Route::get('nusalens/companies/{symbol}/score', ResearchPriorityController::class)
        ->where('symbol', '[A-Za-z0-9]{4}')->middleware('throttle:10,1')->name('nusalens.companies.score');
    Route::get('nusalens/evidence/{evidence}', [ResearchPriorityController::class, 'evidence'])
        ->whereUlid('evidence')->middleware('throttle:60,1')->name('nusalens.evidence');
    Route::get('nusalens/companies/search', [CompanySearchController::class, 'suggestions'])
        ->middleware('throttle:60,1')->name('nusalens.companies.search');
    Route::get('perusahaan', [CompanySearchController::class, 'redirectToDiscover'])->middleware('throttle:60,1')->name('companies');
    Route::get('nusalens/companies/{symbol}/analysis', CompanyAnalyticsController::class)
        ->where('symbol', '[A-Za-z0-9]{4}')->middleware('throttle:60,1')->name('nusalens.companies.analysis');
    Route::get('perusahaan/{symbol}', CompanyProfileController::class)
        ->where('symbol', '[A-Za-z0-9]{4}')->middleware('throttle:60,1')->name('companies.show');
    Route::get('nusalens/companies/{symbol}/research', ResearchSummaryController::class)
        ->where('symbol', '[A-Za-z0-9]{4}')->middleware('throttle:60,1')->name('nusalens.companies.research');

    Route::get('bandingkan', function (Request $request, ComparisonSelectionBuilder $comparison) {
        $validated = $request->validate([
            'symbols' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $payload = $comparison->build((string) $request->user()->id, $validated['symbols'] ?? null);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'symbols' => $exception->getMessage(),
            ]);
        }

        return Inertia::render('nusalens/compare', [
            'comparison' => $payload,
        ]);
    })->middleware('throttle:60,1')->name('compare');
    Route::get('bandingkan/snapshots', [ComparisonSnapshotController::class, 'index'])->middleware('throttle:60,1')->name('compare.snapshots.index');
    Route::post('bandingkan/snapshots', [ComparisonSnapshotController::class, 'store'])->middleware('throttle:snapshot-write')->name('compare.snapshots.store');
    Route::get('bandingkan/snapshots/{snapshot}', [ComparisonSnapshotController::class, 'show'])->middleware('throttle:60,1')->name('compare.snapshots.show');
    Route::patch('bandingkan/snapshots/{snapshot}', [ComparisonSnapshotController::class, 'update'])->middleware('throttle:snapshot-write')->name('compare.snapshots.update');
    Route::delete('bandingkan/snapshots/{snapshot}', [ComparisonSnapshotController::class, 'destroy'])->middleware('throttle:snapshot-write')->name('compare.snapshots.destroy');

    Route::get('jelaskan-nilai', function (Request $request) {
        $validated = $request->validate([
            'symbol' => ['nullable', 'string', 'regex:/\A[A-Za-z0-9]{4}(?:\.JK)?\z/i'],
        ]);

        if (! empty($validated['symbol'])) {
            $symbol = preg_replace('/\.JK$/i', '', strtoupper($validated['symbol']));

            return redirect()->route('companies.show', ['symbol' => $symbol]);
        }

        return redirect()->route('discover');
    })->name('research');

    Route::get('kandidat-menarik', PeerCandidatesController::class)->middleware('throttle:30,1')->name('candidates');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
