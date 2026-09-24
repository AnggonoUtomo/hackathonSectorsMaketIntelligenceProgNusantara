<?php

namespace Tests\Feature\MarketData;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CompanyAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inertia.ssr.enabled' => false, 'services.sectors.api_key' => 'test-key',
            'services.sectors.base_url' => 'https://provider.test/v2']);
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());
    }

    private function url(string $section): string
    {
        return '/nusalens/companies/BBCA/analysis?section='.$section;
    }

    public function test_guests_cannot_fetch_analytics(): void
    {
        auth()->logout();
        $this->getJson($this->url('prices'))->assertUnauthorized();
        Http::assertNothingSent();
    }

    public function test_nonbank_zero_debt_is_not_missing_and_has_no_bank_metrics(): void
    {
        Http::fake(['*' => Http::response([['symbol' => 'ADES.JK', 'date' => '2026-06-30',
            'revenue' => 1085124000000, 'earnings' => 274698000000, 'total_debt' => 0]])]);
        $this->getJson('/nusalens/companies/ADES/analysis?section=financials')->assertOk()
            ->assertJsonPath('rows.0.total_debt', 0)->assertJsonPath('rows.0.gross_loan', null)
            ->assertJsonPath('rows.0.revenue', 1085124000000);
    }

    public function test_prices_sort_preserve_nulls_and_reuse_cache_with_original_timestamp(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 24)->startOfDay());
        Http::fake(['*' => Http::response([
            ['symbol' => 'BBCA.JK', 'date' => '2026-09-23', 'close' => null, 'volume' => 0],
            ['symbol' => 'BBCA.JK', 'date' => '2026-09-22', 'close' => 6000, 'volume' => 100],
        ])]);
        $first = $this->getJson($this->url('prices'))->assertOk()
            ->assertJsonPath('rows.0.date', '2026-09-22')->assertJsonPath('rows.1.close', null)
            ->assertJsonPath('rows.1.volume', 0)->assertJsonPath('range.end', '2026-09-24')->json('fetchedAt');
        $this->travel(30)->minutes();
        $this->getJson($this->url('prices'))->assertOk()->assertJsonPath('fetchedAt', $first);
        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => $r['end'] === '2026-09-24' && $r['start'] === '2026-06-27');
        $this->travel(31)->minutes();
        $this->getJson($this->url('prices'))->assertOk();
        Http::assertSentCount(2);
        $this->assertDatabaseCount('market_data_credit_reservations', 2);
    }

    public function test_quarters_keep_sector_metrics_and_cost_four_with_24_hour_cache(): void
    {
        Http::fake(['*' => Http::response([
            ['symbol' => 'BBCA.JK', 'date' => '2026-06-30', 'revenue' => 200, 'earnings' => -10,
                'financials_sector_metrics' => ['net_interest_income' => 100, 'gross_loan' => 900]],
            ['symbol' => 'BBCA.JK', 'date' => '2026-03-31', 'revenue' => 150, 'earnings' => null],
        ])]);
        $this->getJson($this->url('financials'))->assertOk()->assertJsonPath('rows.0.earnings', null)
            ->assertJsonPath('rows.1.earnings', -10)->assertJsonPath('rows.1.net_interest_income', 100);
        Http::assertSent(fn ($r) => $r['n_quarters'] === 4 && $r['approx'] === 'false');
        $this->assertDatabaseHas('market_data_credit_reservations', ['estimated_credits' => 4]);
        $this->travel(2)->hours();
        $this->getJson($this->url('financials'))->assertOk();
        Http::assertSentCount(1);
        $this->travel(23)->hours();
        $this->getJson($this->url('financials'))->assertOk();
        Http::assertSentCount(2);
    }

    public function test_valuation_requests_one_section_and_labels_historical_years(): void
    {
        Http::fake(['*' => Http::response(['symbol' => 'BBCA.JK', 'valuation' => [
            'historical_valuation' => [['year' => 2025, 'pe' => 12.1, 'pb' => null]],
        ]])]);
        $this->getJson($this->url('valuation'))->assertOk()->assertJsonPath('rows.0.date', '2025')
            ->assertJsonPath('rows.0.pe', 12.1)->assertJsonPath('rows.0.pb', null);
        Http::assertSent(fn ($r) => $r['sections'] === 'valuation');
        $this->assertDatabaseHas('market_data_credit_reservations', ['estimated_credits' => 1]);
    }

    public function test_invalid_sections_auth_and_credit_limits_do_not_fetch(): void
    {
        $this->getJson($this->url('all'))->assertUnprocessable();
        config(['marketdata.credits.daily_user_quota' => 3]);
        $this->getJson($this->url('financials'))->assertStatus(429)->assertJsonPath('reason', 'credit_limit');
        $this->actingAs(User::factory()->unverified()->create());
        $this->getJson($this->url('prices'))->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_malformed_wrong_symbol_duplicate_dates_and_failure_are_not_empty(): void
    {
        Http::fakeSequence()->push(['error' => 'bad'])
            ->push([['symbol' => 'ADES.JK', 'date' => '2026-09-22']])
            ->push([['symbol' => 'BBCA.JK', 'date' => '2026-02-30']])
            ->push([['symbol' => 'BBCA.JK', 'date' => '2026-09-22'], ['symbol' => 'BBCA.JK', 'date' => '2026-09-22']])
            ->push([], 429)->push([]);
        for ($i = 0; $i < 4; $i++) {
            $this->getJson($this->url('prices'))->assertStatus(502)->assertJsonPath('reason', 'invalid_response');
        }
        $this->getJson($this->url('prices'))->assertStatus(429);
        $this->getJson($this->url('prices'))->assertOk()->assertJsonPath('rows', []);
    }
}
