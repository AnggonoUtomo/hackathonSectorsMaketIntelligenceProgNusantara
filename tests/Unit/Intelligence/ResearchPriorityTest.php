<?php

namespace Tests\Unit\Intelligence;

use App\Modules\Intelligence\Domain\MetricNormalizer;
use App\Modules\Intelligence\Domain\ResearchPriorityCalculator;
use PHPUnit\Framework\TestCase;

class ResearchPriorityTest extends TestCase
{
    public function test_ties_all_peers_and_original_completeness_weights(): void
    {
        $rows = $this->population();
        $result = (new ResearchPriorityCalculator)->calculate('TARG', $rows);
        $this->assertEqualsWithDelta(65, $result['completeness'], 0.000001);
        $this->assertNull($result['score']);
        $this->assertSame(50.0, $result['components'][0]['score']);
        $this->assertCount(5, $result['components'][0]['metrics'][0]['peers']);
        $rows[] = $this->company('MORE');
        $this->assertCount(6, (new ResearchPriorityCalculator)->calculate('TARG', $rows)['components'][0]['metrics'][0]['peers']);
    }

    public function test_four_peers_or_duplicate_symbols_do_not_qualify(): void
    {
        $rows = array_slice($this->population(), 0, 5);
        $rows[] = $rows[1];
        $result = (new ResearchPriorityCalculator)->calculate('TARG', $rows);
        $this->assertSame(0.0, $result['completeness']);
        $this->assertNull($result['components'][0]['score']);
    }

    public function test_latest_period_broader_group_before_older_narrow_group_and_no_mixed_banks(): void
    {
        $rows = $this->population();
        foreach ($rows as $i => &$row) {
            $row['groups']['subindustry'] = $i > 2 ? 'Other' : 'Coal Production';
            $row['annual']['2024'] = $row['annual']['2025'];
        }
        unset($row);
        $metric = (new ResearchPriorityCalculator)->calculate('TARG', $rows)['components'][0]['metrics'][0];
        $this->assertSame('industry', $metric['groupLevel']);
        $this->assertSame('2025', $metric['period']);
        $rows[1]['kind'] = 'bank';
        $this->assertNull((new ResearchPriorityCalculator)->calculate('TARG', $rows)['components'][0]['score']);
    }

    public function test_exact_threshold_and_lower_is_better_with_no_intermediate_rounding(): void
    {
        $rows = $this->population();
        foreach ($rows as &$row) {
            $row['annual']['2025']['roe'] = null;
            $row['valuation'] = [['period' => 'Q2-2026', 'priceDate' => '2026-10-02', 'marketCap' => 100, 'earningsTTM' => 10, 'equity' => 50, 'price' => 1]];
        }
        unset($row);
        $rows[0]['valuation'][0]['marketCap'] = 50;
        $result = (new ResearchPriorityCalculator)->calculate('TARG', $rows);
        $this->assertEqualsWithDelta(70, $result['completeness'], 0.000001);
        $this->assertNotNull($result['score']);
        $this->assertSame(100.0, $result['components'][2]['score']);
        $rows[0]['annual']['2025']['currentAssets'] = null;
        $this->assertNull((new ResearchPriorityCalculator)->calculate('TARG', $rows)['score']);
    }

    public function test_negative_denominators_turnaround_and_unadjusted_prices_are_unavailable(): void
    {
        $row = $this->company('TARG');
        $row['annual']['2025']['equity'] = -1;
        $row['quarterly']['Q2-2025']['earnings'] = -2;
        $row['prices'] = array_fill(0, 21, ['close' => 100]);
        $metrics = (new MetricNormalizer)->normalize($row);
        $this->assertNull($metrics['roe'][0]['value']);
        $this->assertNull($metrics['earningsGrowth'][0]['value']);
        $this->assertNull($metrics['momentum'][0]['value']);
        $row['annual']['2025']['equity'] = 100;
        $row['annual']['2025']['roe'] = -0.1;
        $this->assertSame(-10.0, (new MetricNormalizer)->normalize($row)['roe'][0]['value']);
    }

    public function test_nonbank_financial_risk_is_not_reweighted_out_of_completeness(): void
    {
        $rows = $this->population();
        foreach ($rows as &$row) {
            $row['kind'] = 'financial';
        }
        unset($row);
        $result = (new ResearchPriorityCalculator)->calculate('TARG', $rows);
        $this->assertEqualsWithDelta(55, $result['completeness'], 0.000001);
        $this->assertNull($result['components'][4]['score']);
    }

    private function population(): array
    {
        return array_map($this->company(...), ['TARG', 'AAA1', 'AAA2', 'AAA3', 'AAA4', 'AAA5']);
    }

    private function company(string $symbol): array
    {
        return ['symbol' => $symbol, 'name' => $symbol, 'kind' => 'nonfinancial',
            'groups' => ['sector' => 'Energy', 'subsector' => 'Oil, Gas & Coal', 'industry' => 'Coal', 'subindustry' => 'Coal Production'],
            'annual' => ['2025' => ['roe' => 0.2, 'roa' => 0.1, 'equity' => 100, 'assets' => 200, 'debt' => 20, 'currentAssets' => 50, 'currentLiabilities' => 10]],
            'quarterly' => ['Q2-2026' => ['revenue' => 120, 'earnings' => 12], 'Q2-2025' => ['revenue' => 100, 'earnings' => 10]],
            'periods' => ['annual' => ['2025', '2024'], 'quarterly' => ['Q2-2026', 'Q1-2026']],
            'fetchedAt' => '2026-10-02T08:00:00Z'];
    }
}
