<?php

namespace Tests\Feature\Research;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ResponseSequence;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ResearchSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inertia.ssr.enabled' => false, 'services.sectors.api_key' => 'test-key',
            'services.sectors.base_url' => 'https://provider.test/v2']);
        Http::preventStrayRequests();
    }

    private function fakeSources(?ResponseSequence $financials = null): void
    {
        Http::fake([
            'https://provider.test/v2/company/report/*' => Http::response([
                'symbol' => 'ADES.JK', 'company_name' => 'Akasha Wira International',
                'overview' => ['sector' => 'Consumer Non-Cyclicals', 'sub_sector' => 'Food & Beverage'],
            ]),
            'https://provider.test/v2/financials/quarterly/*' => $financials ?? Http::response([
                ['symbol' => 'ADES.JK', 'date' => '2026-06-30', 'revenue' => 100, 'earnings' => 10, 'total_equity' => 80],
            ]),
        ]);
    }

    public function test_auth_and_verification_are_required_before_loading_sources(): void
    {
        $url = '/nusalens/companies/ADES/research';
        $this->getJson($url)->assertUnauthorized();
        $this->actingAs(User::factory()->unverified()->create())->getJson($url)->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_real_adapters_share_cached_inputs_and_preserve_source_timestamps(): void
    {
        $this->travelTo(now('Asia/Jakarta')->setDate(2026, 9, 25)->setTime(10, 0));
        $this->fakeSources();
        $this->actingAs(User::factory()->create());
        $first = $this->getJson('/nusalens/companies/ades/research')->assertOk()
            ->assertJsonPath('symbol', 'ADES')->assertJsonPath('ruleVersion', 'research-facts-v1')
            ->assertJsonPath('companyKind', 'non_financial')->assertJsonPath('score', null)
            ->assertJsonPath('findings.0.evidence.0.value', 10)->json();
        $this->travel(20)->minutes();
        $this->getJson('/nusalens/companies/ADES/research')->assertOk()
            ->assertJsonPath('sources.financials.fetchedAt', $first['sources']['financials']['fetchedAt']);
        $this->getJson('/nusalens/companies/ADES/analysis?section=financials')->assertOk();
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'company/report/') && $r['sections'] === 'overview');
        Http::assertSent(fn ($r) => str_contains($r->url(), 'financials/quarterly/') && $r['n_quarters'] === 4 && $r['approx'] === 'false');
        $this->assertDatabaseCount('market_data_credit_reservations', 2);
        $this->assertDatabaseHas('market_data_credit_reservations', ['estimated_credits' => 4]);
    }

    public function test_provider_failure_is_not_an_empty_research_summary(): void
    {
        Http::fake(['*' => Http::response(['message' => 'secret-provider-detail'], 500)]);
        $this->actingAs(User::factory()->create())->getJson('/nusalens/companies/ADES/research')
            ->assertStatus(502)->assertJsonPath('reason', 'provider_unavailable')
            ->assertDontSee('secret-provider-detail')->assertDontSee('test-key');
    }

    public function test_credit_limit_and_invalid_symbol_do_not_bypass_guards(): void
    {
        $this->actingAs(User::factory()->create());
        $this->getJson('/nusalens/companies/INVALID/research')->assertNotFound();
        config(['marketdata.credits.daily_user_quota' => 0]);
        $this->getJson('/nusalens/companies/ADES/research')->assertStatus(429)->assertJsonPath('reason', 'credit_limit');
        Http::assertNothingSent();
    }

    public function test_empty_financials_remain_empty_and_financial_failure_has_error_status(): void
    {
        $this->fakeSources(Http::sequence()->push([], 503)->push([]));
        $this->actingAs(User::factory()->create());
        $this->getJson('/nusalens/companies/ADES/research')->assertStatus(502)->assertJsonPath('reason', 'provider_unavailable');
        $this->getJson('/nusalens/companies/ADES/research')->assertOk()->assertJsonPath('state', 'empty')->assertJsonPath('findings', []);
    }

    public function test_quarterly_quota_failure_does_not_fetch_financials_or_return_fake_findings(): void
    {
        $this->fakeSources();
        config(['marketdata.credits.daily_user_quota' => 4]);
        $this->actingAs(User::factory()->create());
        $this->getJson('/nusalens/companies/ADES/research')->assertStatus(429)->assertJsonPath('reason', 'credit_limit');
        Http::assertSentCount(1);
        $this->assertDatabaseCount('market_data_credit_reservations', 1);
    }
}
