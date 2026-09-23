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

    public function test_discover_and_companies_show_real_directory(): void
    {
        foreach (['/temukan-saham', '/perusahaan'] as $path) {
            $this->actingAs(User::factory()->create())->get($path)->assertOk()
                ->assertInertia(fn (AssertableInertia $page) => $page
                    ->component('nusalens/discover')
                    ->where('result.items.0.symbol', 'BBCA')
                    ->where('error', null));
        }
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
