<?php

namespace Tests\Feature\MarketData;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inertia.ssr.enabled' => false]);
        config(['services.sectors.api_key' => 'test-key', 'services.sectors.base_url' => 'https://provider.test/v2']);
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());
    }

    private function searchPayload(): array
    {
        return ['results' => [['symbol' => 'BBCA.JK', 'company_name' => 'PT Bank Central Asia Tbk.']],
            'pagination' => ['total_count' => 21, 'limit' => 8, 'offset' => 8]];
    }

    public function test_partial_name_search_uses_structured_query_and_provider_pagination(): void
    {
        Http::fake(['*' => Http::response($this->searchPayload())]);
        $this->getJson('/nusalens/companies/search?q=central&page=2&limit=8')->assertOk()
            ->assertJsonPath('items.0.symbol', 'BBCA')->assertJsonPath('total', 21)->assertJsonPath('page', 2);
        Http::assertSent(fn ($request) => ! isset($request['q']) && $request['offset'] === 8
            && str_contains($request['where'], "company_name like '%central%'")
            && str_contains($request['where'], "symbol like '%central%'"));
        $this->assertDatabaseCount('market_data_credit_reservations', 1);
    }

    public function test_cache_reuse_and_expiry_reserve_per_upstream_attempt(): void
    {
        Http::fake(['*' => Http::response($this->searchPayload())]);
        $this->getJson('/nusalens/companies/search?q=central')->assertOk();
        $this->getJson('/nusalens/companies/search?q=central')->assertOk();
        Http::assertSentCount(1);
        $this->travel(61)->minutes();
        $this->getJson('/nusalens/companies/search?q=central')->assertOk();
        Http::assertSentCount(2);
        $this->assertDatabaseCount('market_data_credit_reservations', 2);
    }

    public function test_invalid_input_never_reaches_provider(): void
    {
        foreach (['a', "bank' or 1=1", 'bank%', 'bank\\', str_repeat('a', 101)] as $query) {
            $this->getJson('/nusalens/companies/search?'.http_build_query(['q' => $query]))->assertUnprocessable();
        }
        Http::assertNothingSent();
    }

    public function test_provider_failure_and_malformed_data_are_not_empty_results(): void
    {
        Http::fakeSequence()->push([], 429)->push(['unexpected' => []], 200);
        $this->getJson('/nusalens/companies/search?q=bank')->assertStatus(429)->assertJsonStructure(['message']);
        $this->getJson('/nusalens/companies/search?q=central')->assertStatus(502)->assertJsonStructure(['message']);
    }

    public function test_credit_quota_blocks_upstream(): void
    {
        config(['marketdata.credits.daily_user_quota' => 0]);
        $this->getJson('/nusalens/companies/search?q=bank')->assertStatus(429);
        Http::assertNothingSent();
    }

    public function test_detail_reads_only_overview_with_correct_units(): void
    {
        Http::fake(['*' => Http::response(['symbol' => 'BBCA.JK', 'company_name' => 'Bank Central Asia',
            'overview' => ['sector' => 'Financials', 'sub_sector' => 'Banks', 'last_close_price' => 6200,
                'daily_close_change' => -0.004, 'latest_close_date' => '2026-09-22', 'website' => 'www.bca.co.id']])]);
        $this->get('/perusahaan/BBCA')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('nusalens/company-profile')->where('company.symbol', 'BBCA')
            ->where('company.price', 6200)->where('company.changePercent', -0.4)->where('error', null));
        Http::assertSent(fn ($request) => $request['sections'] === 'overview');
        $this->assertDatabaseCount('market_data_credit_reservations', 1);
    }

    public function test_search_requires_verification(): void
    {
        $this->actingAs(User::factory()->unverified()->create());
        $this->getJson('/nusalens/companies/search?q=bank')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_company_not_found_is_explicit(): void
    {
        Http::fake(['*' => Http::response([], 404)]);
        $this->get('/perusahaan/ZZZZ')->assertNotFound()->assertInertia(fn (Assert $page) => $page
            ->component('nusalens/company-profile')->where('company', null)->where('error.reason', 'not_found'));
    }

    public function test_empty_result_is_distinct_from_provider_failure(): void
    {
        Http::fake(['*' => Http::response(['results' => [], 'pagination' => ['total_count' => 0]])]);
        $this->getJson('/nusalens/companies/search?q=zzzz')->assertOk()
            ->assertJsonPath('items', [])->assertJsonPath('total', 0);
    }

    public function test_profile_preserves_missing_values_and_rejects_unsafe_links(): void
    {
        Http::fake(['*' => Http::response(['symbol' => 'ADES.JK', 'company_name' => 'Akasha Wira International',
            'overview' => ['sector' => 'Consumer Non-Cyclicals', 'website' => 'javascript:alert(1)']])]);
        $this->get('/perusahaan/ades?from=https://example.test')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('nusalens/company-profile')
                ->where('company.price', null)->where('company.website', null)->where('company.symbol', 'ADES')
                ->where('returnTo', '/temukan-saham'));
    }

    public function test_pagination_and_detail_return_context_are_preserved(): void
    {
        Http::fake(['*' => Http::response(['symbol' => 'BBCA.JK', 'company_name' => 'Bank Central Asia',
            'overview' => ['sector' => 'Financials']])]);
        $from = '/temukan-saham?keyword=bank&page=2&limit=10';
        $this->get('/perusahaan/BBCA?'.http_build_query(['from' => $from]))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('nusalens/company-profile')->where('returnTo', $from));
    }
}
