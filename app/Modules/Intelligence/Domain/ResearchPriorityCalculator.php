<?php

namespace App\Modules\Intelligence\Domain;

use InvalidArgumentException;

final class ResearchPriorityCalculator
{
    public const VERSION = 'nusalens-v1.1.0';

    public const MINIMUM_COMPLETENESS = 60;

    public function calculate(string $symbol, array $companies): array
    {
        $population = [];
        $normalized = [];
        foreach ($companies as $company) {
            $population[$company['symbol']] = $company;
            $normalized[$company['symbol']] = (new MetricNormalizer)->normalize($company);
        }
        $target = $population[$symbol] ?? throw new InvalidArgumentException('Target tidak ada dalam populasi.');
        $definitions = [
            ['quality', 'Kesehatan Bisnis', 30, ['roe' => ['ROE tahunan', true], 'roa' => ['ROA tahunan', true]]],
            ['growth', 'Pertumbuhan', 25, ['revenueGrowth' => ['Pendapatan kuartalan YoY', true], 'earningsGrowth' => ['Laba kuartalan YoY', true]]],
            ['value', 'Harga Saham', 20, ['pe' => ['P/E TTM', false], 'pb' => ['P/B MRQ', false]]],
            ['market', 'Kekuatan Pasar', 15, ['momentum' => ['Perubahan harga 20 sesi', true]]],
            ['risk', 'Keamanan Keuangan', 10, $target['kind'] === 'bank'
                ? ['car' => ['CAR tahunan', true], 'npl' => ['Rasio NPL tahunan', false]]
                : ['der' => ['Utang berbunga / ekuitas', false], 'currentRatio' => ['Rasio lancar', true]]],
        ];
        $components = [];
        $completeness = 0.0;
        $weighted = 0.0;
        $availableWeight = 0;
        foreach ($definitions as [$key, $label, $weight, $definitionsOfMetrics]) {
            $metrics = [];
            foreach ($definitionsOfMetrics as $metric => [$metricLabel, $higher]) {
                $metrics[] = $this->rank($symbol, $metric, $population, $normalized, $higher)
                    + ['key' => $metric, 'label' => $metricLabel, 'higherIsBetter' => $higher, 'originalWeight' => 1 / count($definitionsOfMetrics)];
            }
            $available = array_filter($metrics, fn ($m) => $m['percentile'] !== null);
            $componentScore = count($available) ? array_sum(array_column($available, 'percentile')) / count($available) : null;
            $completeness += $weight * count($available) / count($metrics);
            if ($componentScore !== null) {
                $weighted += $weight * $componentScore;
                $availableWeight += $weight;
            }
            foreach ($metrics as &$metric) {
                $metric['effectiveWeight'] = $metric['percentile'] === null ? 0 : 1 / count($available);
                $metric['contribution'] = $metric['percentile'] === null ? null : $metric['percentile'] * $metric['effectiveWeight'];
            }
            unset($metric);
            $components[] = ['key' => $key, 'label' => $label, 'weight' => $weight, 'score' => $componentScore, 'metrics' => $metrics];
        }
        foreach ($components as &$component) {
            $component['effectiveWeight'] = $component['score'] === null ? 0 : $component['weight'] / $availableWeight;
            $component['contribution'] = $component['score'] === null ? null : $component['score'] * $component['effectiveWeight'];
        }
        unset($component);

        return ['symbol' => $symbol, 'name' => $target['name'], 'formulaVersion' => self::VERSION,
            'score' => $completeness >= self::MINIMUM_COMPLETENESS ? $weighted / $availableWeight : null,
            'completeness' => $completeness, 'components' => $components,
            'reason' => $completeness < self::MINIMUM_COMPLETENESS
                ? 'Data belum cukup. Nilai total memerlukan kelengkapan berbobot minimal '.self::MINIMUM_COMPLETENESS.'%.' : null];
    }

    private function rank(string $symbol, string $metric, array $population, array $normalized, bool $higher): array
    {
        $target = $population[$symbol];
        $attempts = [];
        $observations = $normalized[$symbol][$metric] ?? [];
        foreach ($observations as $observation) {
            if ($observation['value'] === null) {
                continue;
            }
            foreach (['subindustry', 'industry', 'subsector', 'sector'] as $level) {
                $group = $target['groups'][$level] ?? null;
                if (! is_string($group) || $group === '') {
                    continue;
                }
                if (in_array($metric, ['pe', 'pb'], true) && isset($target['valuationGroups'])
                    && ! in_array(['period' => $observation['period'], 'level' => $level, 'group' => $group], $target['valuationGroups'], true)) {
                    continue;
                }
                $peers = [];
                $excluded = [];
                foreach ($population as $peerSymbol => $peer) {
                    if ($peerSymbol === $symbol || ($peer['groups'][$level] ?? null) !== $group) {
                        continue;
                    }
                    if ($peer['kind'] !== $target['kind'] || ! $this->compatible($target, $peer)) {
                        $excluded[] = ['symbol' => $peerSymbol, 'reason' => 'Jenis bisnis tidak kompatibel.'];

                        continue;
                    }
                    $matching = null;
                    foreach ($normalized[$peerSymbol][$metric] ?? [] as $candidate) {
                        if ($candidate['value'] !== null && $candidate['period'] === $observation['period']
                            && $candidate['basis'] === $observation['basis'] && $candidate['unit'] === $observation['unit']
                            && ($candidate['priceDate'] ?? null) === ($observation['priceDate'] ?? null)) {
                            $matching = $candidate;
                            break;
                        }
                    }
                    if ($matching === null) {
                        $excluded[] = ['symbol' => $peerSymbol, 'reason' => 'Input atau periode tidak kompatibel.'];

                        continue;
                    }
                    $peers[] = ['symbol' => $peerSymbol, 'name' => $peer['name']] + $matching;
                }
                $attempts[] = ['period' => $observation['period'], 'level' => $level, 'group' => $group, 'validPeers' => count($peers)];
                if (count($peers) < 5) {
                    continue;
                }
                $values = array_column($peers, 'value');
                $less = count(array_filter($values, fn ($value) => $value < $observation['value']));
                $equal = count(array_filter($values, fn ($value) => $value === $observation['value'])) + 1;
                $rank = 1 + $less + ($equal - 1) / 2;
                $percentile = 100.0 * ($rank - 1) / count($peers);

                return array_merge($observation, ['percentile' => $higher ? $percentile : 100.0 - $percentile,
                    'rank' => $rank, 'populationSize' => count($peers) + 1, 'groupLevel' => $level, 'group' => $group,
                    'peers' => $peers, 'excluded' => $excluded, 'attempts' => $attempts]);
            }
        }
        $observation = $observations[0] ?? ['value' => null, 'period' => '', 'basis' => '', 'unit' => '', 'inputs' => [], 'fetchedAt' => null];

        return array_merge($observation, ['percentile' => null, 'rank' => null, 'populationSize' => null,
            'groupLevel' => null, 'group' => null, 'peers' => [], 'excluded' => [], 'attempts' => $attempts,
            'reason' => $observation['reason'] ?? 'Kurang dari lima peer lain dengan input dan periode yang sesuai.']);
    }

    public static function compatible(array $target, array $peer): bool
    {
        // A broad sector alone does not establish comparable business models.
        $key = $target['kind'] === 'financial' ? 'industry' : 'subsector';

        return ! empty($target['groups'][$key]) && ($target['groups'][$key] === ($peer['groups'][$key] ?? null));
    }
}
