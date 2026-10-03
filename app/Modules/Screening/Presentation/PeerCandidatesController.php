<?php

namespace App\Modules\Screening\Presentation;

use App\Modules\Intelligence\Application\Contracts\PeerResearch;
use Illuminate\Http\Request;
use Inertia\Inertia;

final class PeerCandidatesController
{
    public function __invoke(Request $request, PeerResearch $research)
    {
        $filters = $request->validate(['evidence' => ['nullable', 'ulid'], 'component' => ['nullable', 'in:quality,growth,risk'],
            'q' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1', 'max:1000']]);
        if (empty($filters['evidence'])) {
            return redirect()->route('discover');
        }
        $result = $research->candidates($filters['evidence']);
        abort_if($result === null, 404);
        $component = $filters['component'] ?? 'quality';
        $query = $filters['q'] ?? '';
        $page = (int) ($filters['page'] ?? 1);
        $items = array_values(array_filter($result['items'], fn ($row) => $query === '' || mb_stripos($row['symbol'].' '.$row['name'], $query) !== false));
        usort($items, fn ($a, $b) => (($b['components'][$component] ?? -1) <=> ($a['components'][$component] ?? -1)) ?: strcmp($a['symbol'], $b['symbol']));
        $result['total'] = count($items);
        $result['items'] = array_slice($items, ($page - 1) * 15, 15);

        return Inertia::render('nusalens/peer-candidates', ['result' => $result, 'filters' => ['q' => $query, 'component' => $component, 'page' => $page]]);
    }
}
