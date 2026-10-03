<?php

namespace App\Modules\Intelligence\Presentation;

use App\Modules\Intelligence\Application\CalculateResearchPriority;
use App\Modules\Intelligence\Application\Contracts\ScoreEvidence;
use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use Illuminate\Http\Request;

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

    public function evidence(string $evidence, ScoreEvidence $store)
    {
        $record = $store->find($evidence);
        abort_if($record === null, 404);

        return response()->json($record);
    }
}
