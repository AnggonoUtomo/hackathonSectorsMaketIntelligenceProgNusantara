<?php

namespace App\Modules\Intelligence\Presentation;

use App\Modules\Intelligence\Application\CalculateResearchPriority;
use App\Modules\Intelligence\Application\Contracts\ScoreEvidence;
use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use Illuminate\Http\Request;
use Inertia\Inertia;

final class ResearchPriorityController
{
    public function __invoke(Request $request, string $symbol, CalculateResearchPriority $calculate)
    {
        $validated = $request->validate(['include_market' => ['sometimes', 'boolean']]);
        try {
            return response()->json($calculate->execute((string) $request->user()->id, $symbol, (bool) ($validated['include_market'] ?? false)));
        } catch (MarketDataUnavailable $e) {
            return response()->json($e->details(), $e->status);
        }
    }

    public function evidence(Request $request, string $evidence, ScoreEvidence $store)
    {
        $record = $store->find($evidence);
        abort_if($record === null && $request->wantsJson(), 404);

        $response = $request->wantsJson()
            ? response()->json($record)
            : Inertia::render('nusalens/research-evidence', ['evidence' => $record])
                ->toResponse($request)->setStatusCode($record === null ? 404 : 200);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
