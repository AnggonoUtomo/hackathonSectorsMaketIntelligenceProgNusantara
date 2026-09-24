<?php

namespace App\Modules\Company\Presentation;

use App\Modules\Company\Application\GetCompanyAnalytics;
use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyAnalyticsController
{
    public function __invoke(Request $request, string $symbol, GetCompanyAnalytics $analytics): JsonResponse
    {
        $input = $request->validate(['section' => ['required', Rule::in(['prices', 'financials', 'valuation'])]]);
        try {
            return response()->json($analytics->execute((string) $request->user()->id, $symbol, $input['section']));
        } catch (MarketDataUnavailable $error) {
            return response()->json($error->details(), $error->status);
        }
    }
}
