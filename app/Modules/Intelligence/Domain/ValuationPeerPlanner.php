<?php

namespace App\Modules\Intelligence\Domain;

final class ValuationPeerPlanner
{
    public function plans(string $symbol, array $companies): array
    {
        $target = array_values(array_filter($companies, fn ($c) => $c['symbol'] === $symbol))[0];
        $plans = [];
        foreach ($target['periods']['quarterly'] as $period) {
            if (! $this->eligible($target, $period)) {
                continue;
            }
            foreach (['subindustry', 'industry', 'subsector', 'sector'] as $level) {
                $group = $target['groups'][$level] ?? null;
                if (! $group) {
                    continue;
                }
                $symbols = [];
                foreach ($companies as $company) {
                    if (($company['groups'][$level] ?? null) === $group && $company['kind'] === $target['kind']
                        && ResearchPriorityCalculator::compatible($target, $company) && $this->eligible($company, $period)) {
                        $symbols[] = $company['symbol'];
                    }
                }
                sort($symbols);
                if (count($symbols) >= 6) {
                    $plans[] = compact('symbols', 'period', 'level', 'group');
                }
            }
        }

        return $plans;
    }

    private function eligible(array $company, string $period): bool
    {
        if (($company['quarterly'][$period]['equity'] ?? 0) > 0) {
            return true;
        }
        [$quarter, $year] = explode('-', $period);
        $q = (int) substr($quarter, 1);
        $y = (int) $year;
        $total = 0.0;
        for ($i = 0; $i < 4; $i++) {
            $earnings = $company['quarterly']['Q'.$q.'-'.$y]['earnings'] ?? null;
            if (! is_numeric($earnings) || ! is_finite((float) $earnings)) {
                return false;
            }
            $total += $earnings;
            if (--$q === 0) {
                $q = 4;
                $y--;
            }
        }

        return $total > 0;
    }
}
