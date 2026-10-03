<?php

namespace App\Modules\Intelligence\Application;

use App\Modules\Intelligence\Application\Contracts\PeerResearch;
use App\Modules\Intelligence\Application\Contracts\ScoreEvidence;
use App\Modules\Intelligence\Domain\ResearchPriorityCalculator;

final class FindPeerResearch implements PeerResearch
{
    public function __construct(private readonly ScoreEvidence $evidence, private readonly ResearchPriorityCalculator $calculator) {}

    public function candidates(string $evidenceId): ?array
    {
        $evidence = $this->evidence->find($evidenceId);
        if ($evidence === null) {
            return null;
        }
        $population = $evidence['input']['companies'];
        $target = array_values(array_filter($population, fn ($company) => $company['symbol'] === $evidence['input']['symbol']))[0];
        $items = [];
        // Target-specific market coverage is not transferable to other candidates.
        $fundamentals = array_map(function ($item) {
            unset($item['market'], $item['valuation'], $item['valuationGroups']);

            return $item;
        }, $population);
        foreach ($population as $company) {
            if ($company['kind'] !== $target['kind'] || ! ResearchPriorityCalculator::compatible($target, $company)) {
                continue;
            }
            $result = $this->calculator->calculate($company['symbol'], $fundamentals);
            $items[] = ['symbol' => $company['symbol'], 'name' => $company['name'], 'group' => $company['groups']['subindustry'],
                'completeness' => $result['completeness'], 'components' => array_column($result['components'], 'score', 'key')];
        }

        return ['items' => $items, 'target' => $target['symbol'], 'fetchedAt' => $evidence['input']['fetchedAt'],
            'expiresAt' => $evidence['input']['expiresAt'], 'evidenceId' => $evidenceId];
    }
}
