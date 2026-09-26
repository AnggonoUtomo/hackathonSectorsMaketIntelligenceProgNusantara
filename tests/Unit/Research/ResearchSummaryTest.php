<?php

namespace Tests\Unit\Research;

use App\Modules\Research\Domain\ResearchSummary;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ResearchSummaryTest extends TestCase
{
    private function company(): array
    {
        return ['symbol' => 'ADES', 'sector' => 'Consumer Non-Cyclicals', 'subSector' => 'Food & Beverage',
            'industry' => 'Beverages', 'fetchedAt' => '2026-09-25T10:00:00+07:00'];
    }

    private function financials(): array
    {
        return ['rows' => [
            ['date' => '2026-03-31', 'revenue' => 80, 'earnings' => 8, 'total_equity' => 90],
            ['date' => '2026-06-30', 'revenue' => 100, 'earnings' => 12.345, 'total_equity' => 110],
        ], 'fetchedAt' => '2026-09-25T09:00:00+07:00', 'currency' => 'IDR',
            'basis' => ['revenue' => 'quarterly', 'earnings' => 'quarterly', 'total_equity' => 'instant']];
    }

    private function build(?array $company = null, ?array $financials = null): array
    {
        return (new ResearchSummary)->build($company ?? $this->company(), $financials ?? $this->financials(),
            new DateTimeImmutable('2026-09-25T10:30:00+07:00'));
    }

    public function test_facts_have_reproducible_evidence_and_do_not_round_calculations(): void
    {
        $result = $this->build();
        $this->assertSame($result, $this->build());
        $this->assertSame('non_financial', $result['companyKind']);
        $this->assertSame('2026-06-30', $result['period']);
        $margin = array_column($result['findings'], null, 'id')['net_margin'];
        $this->assertEqualsWithDelta(12.345, $margin['value'], 0.0000001);
        $this->assertCount(2, $margin['evidence']);
        $this->assertSame('earnings / revenue * 100', $margin['formula']);
        $this->assertSame('IDR', $margin['evidence'][0]['unit']);
        $this->assertSame('quarterly', $margin['evidence'][0]['basis']);
        $this->assertSame('financials/quarterly/ADES', $result['sources']['financials']['endpoint']);
        $this->assertSame('2026-09-25T09:00:00+07:00', $result['sources']['financials']['fetchedAt']);
        $this->assertNull($result['score']);
        $this->assertContains('cash_basis', array_column($result['checks'], 'id'));
    }

    public static function invalidRevenue(): array
    {
        return [[null], [0], [-100], [INF], [NAN]];
    }

    #[DataProvider('invalidRevenue')]
    public function test_invalid_denominator_does_not_produce_margin(mixed $value): void
    {
        $financials = $this->financials();
        $financials['rows'][1]['revenue'] = $value;
        $result = $this->build(financials: $financials);
        $this->assertNotContains('net_margin', array_column($result['findings'], 'id'));
        $this->assertContains('net_margin', array_column($result['checks'], 'id'));
    }

    public function test_latest_missing_is_not_replaced_by_older_value_and_zero_is_preserved(): void
    {
        $financials = $this->financials();
        $financials['rows'][1]['earnings'] = null;
        $financials['rows'][1]['total_equity'] = 0;
        $result = $this->build(financials: $financials);
        $this->assertNotContains('earnings', array_column($result['findings'], 'id'));
        $this->assertSame('2026-06-30', $result['period']);
        $this->assertSame(0.0, array_column($result['findings'], null, 'id')['equity']['value']);
    }

    public function test_losses_and_negative_equity_remain_facts_not_investment_categories(): void
    {
        $financials = $this->financials();
        $financials['rows'][1]['earnings'] = -10;
        $financials['rows'][1]['total_equity'] = -5;
        $facts = array_column($this->build(financials: $financials)['findings'], null, 'id');
        $this->assertSame('Perusahaan mencatat rugi kuartalan', $facts['earnings']['title']);
        $this->assertSame(-10.0, $facts['net_margin']['value']);
        $this->assertSame('Ekuitas tercatat negatif', $facts['equity']['title']);
    }

    public static function classifications(): array
    {
        return [
            ['Financials', 'Banks', 'Banks', 'bank'],
            ['Financials', 'Insurance', 'Insurance', 'financial_nonbank'],
            [null, null, null, 'unknown'],
            ['Unrecognised sector', null, null, 'unknown'],
        ];
    }

    #[DataProvider('classifications')]
    public function test_other_company_types_do_not_receive_industrial_margin_interpretation(?string $sector, ?string $subSector, ?string $industry, string $kind): void
    {
        $company = array_replace($this->company(), compact('sector', 'subSector', 'industry'));
        $result = $this->build(company: $company);
        $this->assertSame($kind, $result['companyKind']);
        $this->assertNotContains('net_margin', array_column($result['findings'], 'id'));
        $this->assertContains('earnings', array_column($result['findings'], 'id'));
    }

    public function test_unverified_basis_and_currency_do_not_produce_comparisons(): void
    {
        $financials = $this->financials();
        $financials['basis']['revenue'] = 'ytd';
        $result = $this->build(financials: $financials);
        $this->assertNotContains('net_margin', array_column($result['findings'], 'id'));
        $financials['currency'] = 'USD';
        $this->assertSame([], $this->build(financials: $financials)['findings']);
    }

    public function test_stale_data_is_not_described_as_a_current_finding(): void
    {
        $financials = $this->financials();
        $financials['fetchedAt'] = '2026-09-24T10:30:00+07:00';
        $result = $this->build(financials: $financials);
        $this->assertSame([], $result['findings']);
        $this->assertSame('stale', $result['sources']['financials']['status']);
        $this->assertSame('2026-09-24T10:30:00+07:00', $result['sources']['financials']['fetchedAt']);
    }

    public function test_empty_data_and_future_report_are_explicitly_unavailable(): void
    {
        $financials = $this->financials();
        $financials['rows'] = [];
        $this->assertSame('empty', $this->build(financials: $financials)['state']);
        $financials['rows'] = [['date' => '2026-12-31', 'earnings' => 100]];
        $this->assertSame('unavailable', $this->build(financials: $financials)['state']);
    }

    public function test_expired_profile_disables_sector_specific_rules_but_not_financial_facts(): void
    {
        $company = $this->company();
        $company['fetchedAt'] = '2026-09-25T09:30:00+07:00';
        $result = $this->build(company: $company);
        $this->assertSame('unknown', $result['companyKind']);
        $this->assertSame('stale', $result['sources']['profile']['status']);
        $this->assertContains('earnings', array_column($result['findings'], 'id'));
        $this->assertNotContains('net_margin', array_column($result['findings'], 'id'));
    }

    public function test_unsorted_input_uses_latest_report_and_future_fetch_time_is_unknown(): void
    {
        $financials = $this->financials();
        $financials['rows'] = array_reverse($financials['rows']);
        $this->assertSame('2026-06-30', $this->build(financials: $financials)['period']);
        $financials['fetchedAt'] = '2026-09-25T10:31:00+07:00';
        $result = $this->build(financials: $financials);
        $this->assertSame('unknown', $result['sources']['financials']['status']);
        $this->assertSame([], $result['findings']);
    }
}
