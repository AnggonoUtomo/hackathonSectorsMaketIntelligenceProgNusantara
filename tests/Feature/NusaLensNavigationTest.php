<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NusaLensNavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function protectedPages(): array
    {
        return [
            'temukan saham' => ['/temukan-saham'],
            'perusahaan' => ['/perusahaan'],
            'bandingkan' => ['/bandingkan'],
            'jelaskan nilai' => ['/jelaskan-nilai'],
            'kandidat menarik' => ['/kandidat-menarik'],
        ];
    }

    #[DataProvider('protectedPages')]
    public function test_guests_are_redirected_from_nusalens_pages(string $path): void
    {
        $this->get($path)->assertRedirect('/login');
    }

    #[DataProvider('protectedPages')]
    public function test_verified_users_can_visit_nusalens_pages(string $path): void
    {
        $this->actingAs(User::factory()->create());

        $this->get($path)->assertOk();
    }

    #[DataProvider('protectedPages')]
    public function test_unverified_users_are_redirected_from_nusalens_pages(string $path): void
    {
        $user = User::factory()->unverified()->create();

        $this
            ->actingAs($user)
            ->get($path)
            ->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_discover_page_receives_fake_backend_results(): void
    {
        config(['marketdata.provider_mode' => 'fake']);

        $this->actingAs(User::factory()->create());

        $this
            ->get('/temukan-saham?sector=financials&min_score=80&limit=2')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('section', 'discover')
                ->where('discover.filters.sector', 'financials')
                ->where('discover.filters.minScore', '80')
                ->where('discover.results.0.symbol', 'BBCA')
                ->has('discover.results', 1)
                ->where('discover.meta.source', 'backend_fake')
                ->where('discover.meta.estimatedCredits', 1)
            );
    }

    public function test_discover_page_can_return_empty_fake_results(): void
    {
        config(['marketdata.provider_mode' => 'fake']);

        $this->actingAs(User::factory()->create());

        $this
            ->get('/temukan-saham?keyword=ZZZZ')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('discover.filters.keyword', 'ZZZZ')
                ->has('discover.results', 0)
                ->where('discover.meta.state', 'empty')
            );
    }

    public function test_discover_page_rejects_invalid_fake_filter(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->from('/temukan-saham')
            ->get('/temukan-saham?sector=raw-expression')
            ->assertRedirect('/temukan-saham')
            ->assertSessionHasErrors('sector');
    }

    public function test_discover_page_can_use_real_mode_with_fake_http(): void
    {
        Cache::flush();
        config([
            'marketdata.provider_mode' => 'real',
            'services.sectors.api_key' => 'test-sectors-key',
            'services.sectors.base_url' => 'https://api.example.test/v2',
        ]);
        Http::fake([
            'https://api.example.test/v2/companies*' => Http::response([
                'data' => [
                    [
                        'symbol' => 'BBCA',
                        'company_name' => 'Bank Central Asia Tbk',
                        'sector' => 'Financials',
                    ],
                ],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 5,
                    'total' => 1,
                    'last_page' => 1,
                ],
            ]),
        ]);

        $this->actingAs(User::factory()->create());

        $this
            ->get('/temukan-saham?sector=financials&limit=5')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('section', 'discover')
                ->where('discover.meta.source', 'sectors_real')
                ->where('discover.meta.liveProvider', true)
                ->where('discover.results.0.symbol', 'BBCA')
        );

        $this->assertDatabaseCount('market_data_credit_reservations', 1);
        $this->assertCount(1, Http::recorded(fn ($request): bool => str_contains($request->url(), 'api.example.test/v2/companies')));
    }

    public function test_discover_real_mode_cache_hit_does_not_reserve_credit_again(): void
    {
        Cache::flush();
        config([
            'marketdata.provider_mode' => 'real',
            'services.sectors.api_key' => 'test-sectors-key',
            'services.sectors.base_url' => 'https://api.example.test/v2',
        ]);
        Http::fake([
            'https://api.example.test/v2/companies*' => Http::response([
                'data' => [
                    [
                        'symbol' => 'TLKM',
                        'company_name' => 'Telkom Indonesia Tbk',
                        'sector' => 'Infrastructure',
                    ],
                ],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 5,
                    'total' => 1,
                    'last_page' => 1,
                ],
            ]),
        ]);

        $this->actingAs(User::factory()->create());

        $this->get('/temukan-saham?sector=infrastructure&limit=5')->assertOk();
        $this->get('/temukan-saham?sector=infrastructure&limit=5')->assertOk();

        $this->assertDatabaseCount('market_data_credit_reservations', 1);
        $this->assertCount(1, Http::recorded(fn ($request): bool => str_contains($request->url(), 'api.example.test/v2/companies')));
    }

    public function test_company_detail_page_receives_fake_snapshot(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->get('/perusahaan/BBCA')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('section', 'companies')
                ->where('company.symbol', 'BBCA')
                ->where('company.name', 'Bank Central Asia Tbk')
                ->where('company.metrics.0.label', 'Nilai Prioritas Riset')
                ->where('company.meta.source', 'backend_fake')
            );
    }

    public function test_companies_page_receives_company_list(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->get('/perusahaan')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('section', 'companies')
                ->where('companies.0.symbol', 'BBCA')
                ->where('companies.0.sector', 'Financials')
            );
    }

    public function test_unknown_company_detail_returns_pending_snapshot(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->get('/perusahaan/ADES')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('section', 'companies')
                ->where('company.symbol', 'ADES')
                ->where('company.meta.source', 'detail_pending')
            );
    }

    public function test_compare_page_receives_default_fake_payload(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->get('/bandingkan')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('section', 'compare')
                ->where('comparison.meta.state', 'ready')
                ->where('comparison.meta.limit', 3)
                ->where('comparison.symbols.0', 'BBCA')
                ->where('comparison.symbols.1', 'TLKM')
                ->where('comparison.symbols.2', 'ICBP')
                ->has('comparison.companies', 3)
            );
    }

    public function test_compare_page_receives_fake_matrix_for_symbols(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->get('/bandingkan?symbols=BBCA,TLKM')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('section', 'compare')
                ->where('comparison.symbols.0', 'BBCA')
                ->where('comparison.symbols.1', 'TLKM')
                ->where('comparison.companies.0.name', 'Bank Central Asia Tbk')
                ->where('comparison.metrics.0.label', 'Nilai Prioritas Riset')
                ->where('comparison.metrics.0.values.BBCA', '82,45')
                ->where('comparison.meta.state', 'ready')
            );
    }

    public function test_compare_page_receives_pending_matrix_for_real_symbols(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->get('/bandingkan?symbols=ADES,AADI')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('section', 'compare')
                ->where('comparison.symbols.0', 'ADES')
                ->where('comparison.symbols.1', 'AADI')
                ->where('comparison.companies.0.name', 'ADES - data detail belum dimuat')
                ->where('comparison.metrics.0.values.ADES', '-')
                ->where('comparison.meta.state', 'ready')
            );
    }

    public function test_compare_page_rejects_more_than_three_symbols(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->from('/bandingkan')
            ->get('/bandingkan?symbols=BBCA,TLKM,ICBP,BBRI')
            ->assertRedirect('/bandingkan')
            ->assertSessionHasErrors('symbols');
    }

    public function test_compare_page_rejects_invalid_symbol_format(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->from('/bandingkan')
            ->get('/bandingkan?symbols=BBCA,raw-expression')
            ->assertRedirect('/bandingkan')
            ->assertSessionHasErrors('symbols');
    }

    public function test_research_page_receives_default_rule_based_explainer(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->get('/jelaskan-nilai')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('section', 'research')
                ->where('research.symbol', 'BBCA')
                ->where('research.company.name', 'Bank Central Asia Tbk')
                ->where('research.meta.state', 'ready')
                ->where('research.meta.aiEnabled', false)
            );
    }

    public function test_research_page_receives_pending_explainer_for_real_symbol(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->get('/jelaskan-nilai?symbol=ADES')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('section', 'research')
                ->where('research.symbol', 'ADES')
                ->where('research.meta.state', 'pending')
                ->where('research.metrics.0.value', '-')
            );
    }

    public function test_research_page_rejects_invalid_symbol_format(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->from('/jelaskan-nilai')
            ->get('/jelaskan-nilai?symbol=raw-expression')
            ->assertRedirect('/jelaskan-nilai')
            ->assertSessionHasErrors('symbol');
    }
}
