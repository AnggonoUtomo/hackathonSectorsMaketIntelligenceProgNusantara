<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NusaLensNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inertia.ssr.enabled' => false, 'services.sectors.api_key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response([
            'results' => [['symbol' => 'BBCA.JK', 'company_name' => 'Bank Central Asia']],
            'pagination' => ['total_count' => 1, 'limit' => 10, 'offset' => 0],
        ])]);
    }

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

        if ($path === '/perusahaan') {
            $this->get($path)->assertRedirect('/temukan-saham');
            Http::assertNothingSent();

            return;
        }

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

    public function test_discover_shows_real_directory(): void
    {
        $this->actingAs(User::factory()->create())->get('/temukan-saham')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/discover')
                ->where('result.items.0.symbol', 'BBCA')
                ->where('error', null));
    }

    public function test_companies_alias_preserves_only_valid_search_filters_without_fetching(): void
    {
        $filters = ['keyword' => 'Bank Central Asia', 'page' => 2, 'limit' => 8];
        $query = $filters + ['from' => 'https://example.com', 'sections' => 'all'];

        $this->actingAs(User::factory()->create())
            ->get(route('companies', $query, absolute: false))
            ->assertStatus(302)
            ->assertRedirect(route('discover', $filters));

        Http::assertNothingSent();
        $this->assertDatabaseCount('market_data_credit_reservations', 0);
        $this->assertSame('/perusahaan/ADES', route('companies.show', ['symbol' => 'ADES'], absolute: false));
    }

    public function test_following_companies_alias_loads_directory_once(): void
    {
        $this->actingAs(User::factory()->create());
        $response = $this->get('/perusahaan?keyword=Bank&page=1&limit=10');
        $response->assertRedirect(route('discover', ['keyword' => 'Bank', 'page' => 1, 'limit' => 10]));
        Http::assertNothingSent();

        $this->get($response->headers->get('Location'))->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/discover')
                ->where('filters.keyword', 'Bank')
                ->where('filters.page', 1)
                ->where('filters.limit', 10));
        Http::assertSentCount(1);
        $this->assertDatabaseCount('market_data_credit_reservations', 1);
    }

    public static function invalidDirectoryFilters(): array
    {
        return [
            'expression' => [['keyword' => "bank' or 1=1"], 'keyword'],
            'short keyword' => [['keyword' => 'B'], 'keyword'],
            'array keyword' => [['keyword' => ['Bank']], 'keyword'],
            'zero page' => [['page' => 0], 'page'],
            'large page' => [['page' => 10001], 'page'],
            'fractional page' => [['page' => 1.5], 'page'],
            'zero limit' => [['limit' => 0], 'limit'],
            'large limit' => [['limit' => 26], 'limit'],
        ];
    }

    #[DataProvider('invalidDirectoryFilters')]
    public function test_directory_alias_and_canonical_share_validation(array $filters, string $field): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['/perusahaan', '/temukan-saham'] as $path) {
            $this->getJson($path.'?'.http_build_query($filters))
                ->assertUnprocessable()->assertJsonValidationErrors($field);
        }

        Http::assertNothingSent();
        $this->assertDatabaseCount('market_data_credit_reservations', 0);
    }

    public function test_discover_rejects_provider_expressions_as_search_text(): void
    {
        $this->actingAs(User::factory()->create())->from('/temukan-saham')
            ->get('/temukan-saham?'.http_build_query(['keyword' => "bank' or 1=1"]))
            ->assertRedirect('/temukan-saham')->assertSessionHasErrors('keyword');
        Http::assertNothingSent();
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

    public function test_research_page_starts_with_search_without_fake_facts_or_provider_calls(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->get('/jelaskan-nilai')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/research-start')
                ->missing('research')
            );
        Http::assertNothingSent();
    }

    public function test_research_symbol_links_to_real_company_without_fetch_on_redirect(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->get('/jelaskan-nilai?symbol=ADES')
            ->assertRedirect('/perusahaan/ADES');
        Http::assertNothingSent();
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
