<?php

namespace App\Modules\Research\Presentation;

use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use App\Modules\Research\Application\GetResearchSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResearchSummaryController
{
    public function __invoke(Request $request, string $symbol, GetResearchSummary $summary): JsonResponse
    {
        try {
            return response()->json($summary->execute((string) $request->user()->id, $symbol));
        } catch (MarketDataUnavailable $error) {
            return response()->json($error->details(), $error->status);
        }
    }
}
