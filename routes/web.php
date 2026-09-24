<?php

use App\Modules\Company\Presentation\CompanyAnalyticsController;
use App\Modules\Company\Presentation\CompanyProfileController;
use App\Modules\Comparison\Application\FakeComparisonBuilder;
use App\Modules\Research\Application\RuleBasedResearchExplainer;
use App\Modules\Screening\Presentation\CompanySearchController;
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

    Route::get('temukan-saham', [CompanySearchController::class, 'index'])->middleware('throttle:60,1')->name('discover');
    Route::get('nusalens/companies/search', [CompanySearchController::class, 'suggestions'])
        ->middleware('throttle:60,1')->name('nusalens.companies.search');
    Route::get('perusahaan', [CompanySearchController::class, 'index'])->middleware('throttle:60,1')->name('companies');
    Route::get('nusalens/companies/{symbol}/analysis', CompanyAnalyticsController::class)
        ->where('symbol', '[A-Za-z0-9]{4}')->middleware('throttle:60,1')->name('nusalens.companies.analysis');
    Route::get('perusahaan/{symbol}', CompanyProfileController::class)
        ->where('symbol', '[A-Za-z0-9]{4}')->middleware('throttle:60,1')->name('companies.show');

    Route::get('bandingkan', function (Request $request, FakeComparisonBuilder $comparison) {
        $validated = $request->validate([
            'symbols' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $payload = $comparison->build($validated['symbols'] ?? null);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'symbols' => $exception->getMessage(),
            ]);
        }

        return Inertia::render('nusalens/placeholder', [
            'section' => 'compare',
            'comparison' => $payload,
        ]);
    })->name('compare');

    Route::get('jelaskan-nilai', function (Request $request, RuleBasedResearchExplainer $explainer) {
        $validated = $request->validate([
            'symbol' => ['nullable', 'string', 'max:12'],
        ]);

        try {
            $research = $explainer->explain($validated['symbol'] ?? null);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'symbol' => $exception->getMessage(),
            ]);
        }

        return Inertia::render('nusalens/placeholder', [
            'section' => 'research',
            'research' => $research,
        ]);
    })->name('research');

    Route::get('kandidat-menarik', function () {
        return Inertia::render('nusalens/placeholder', ['section' => 'candidates']);
    })->name('candidates');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
