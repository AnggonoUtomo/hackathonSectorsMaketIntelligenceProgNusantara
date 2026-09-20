<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_unknown_company_detail_returns_not_found(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/perusahaan/ZZZZ')->assertNotFound();
    }

    public function test_compare_page_receives_empty_fake_payload(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->get('/bandingkan')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/placeholder')
                ->where('section', 'compare')
                ->where('comparison.meta.state', 'empty')
                ->where('comparison.meta.limit', 3)
                ->has('comparison.companies', 0)
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

    public function test_compare_page_rejects_more_than_three_symbols(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->from('/bandingkan')
            ->get('/bandingkan?symbols=BBCA,TLKM,ICBP,BBRI')
            ->assertRedirect('/bandingkan')
            ->assertSessionHasErrors('symbols');
    }

    public function test_compare_page_rejects_unknown_symbol(): void
    {
        $this->actingAs(User::factory()->create());

        $this
            ->from('/bandingkan')
            ->get('/bandingkan?symbols=BBCA,ZZZZ')
            ->assertRedirect('/bandingkan')
            ->assertSessionHasErrors('symbols');
    }
}
