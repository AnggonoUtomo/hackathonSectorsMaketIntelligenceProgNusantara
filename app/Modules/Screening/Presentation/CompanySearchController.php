<?php

namespace App\Modules\Screening\Presentation;

use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use App\Modules\Screening\Application\SearchCompanies;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanySearchController
{
    public function index(Request $request, SearchCompanies $search)
    {
        $filters = $this->directoryFilters($request);
        $keyword = trim($filters['keyword'] ?? '');
        $page = (int) ($filters['page'] ?? 1);
        $limit = (int) ($filters['limit'] ?? 10);
        $result = null;
        $error = null;
        try {
            $result = $search->execute((string) $request->user()->id, $keyword, $page, $limit);
        } catch (MarketDataUnavailable $exception) {
            $error = $exception->details();
        }

        return Inertia::render('nusalens/discover', [
            'filters' => ['keyword' => $keyword, 'page' => $page, 'limit' => $limit],
            'result' => $result, 'error' => $error,
        ]);
    }

    public function redirectToDiscover(Request $request)
    {
        return redirect()->route('discover', $this->directoryFilters($request));
    }

    private function directoryFilters(Request $request): array
    {
        return $request->validate([
            'keyword' => ['nullable', 'string', 'min:2', 'max:100', 'regex:/\A[\pL\pN .&-]+\z/u'],
            'page' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
        ]);
    }

    public function suggestions(Request $request, SearchCompanies $search)
    {
        $input = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100', 'regex:/\A[\pL\pN .&-]+\z/u'],
            'page' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
        ]);
        try {
            return response()->json($search->execute((string) $request->user()->id, trim($input['q']),
                (int) ($input['page'] ?? 1), (int) ($input['limit'] ?? 8)));
        } catch (MarketDataUnavailable $exception) {
            return response()->json($exception->details(), $exception->status);
        }
    }
}
