<?php

namespace Tests\Feature\Comparison;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class CompareProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config([
            'inertia.ssr.enabled' => false,
            'services.sectors.api_key' => 'test-key',
            'services.sectors.base_url' => 'https://provider.test/v2',
        ]);
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());
    }

    public function test_compare_loads_real_profile_cards_for_three_symbols_with_fake_http(): void
    {
        Http::fakeSequence()
            ->push($this->profilePayload('BBCA.JK', 'Bank Central Asia', 'Financials', 'Banks', 6200, '2026-09-23'))
            ->push($this->profilePayload('TLKM.JK', 'Telkom Indonesia', 'Infrastructure', 'Telecommunication', 3000, '2026-09-23'))
            ->push($this->profilePayload('ICBP.JK', 'Indofood CBP', 'Consumer Non-Cyclicals', 'Processed Foods', 9800, '2026-09-23'));

        $this->get('/bandingkan?symbols=BBCA,TLKM,ICBP')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/compare')
                ->where('comparison.symbols', ['BBCA', 'TLKM', 'ICBP'])
                ->where('comparison.companies.0.symbol', 'BBCA')
                ->where('comparison.companies.0.name', 'Bank Central Asia')
                ->where('comparison.companies.0.sector', 'Financials')
                ->where('comparison.companies.0.subSector', 'Banks')
                ->where('comparison.companies.0.price', 6200)
                ->where('comparison.companies.0.priceDate', '2026-09-23')
                ->where('comparison.companies.0.status', 'ready')
                ->where('comparison.companies.0.error', null)
                ->where('comparison.metrics', [])
                ->where('comparison.meta.source', 'profile')
                ->where('comparison.meta.state', 'ready')
                ->where('comparison.meta.estimatedCredits', 3)
            );

        Http::assertSentCount(3);
        Http::assertSent(fn ($request) => $request['sections'] === 'overview');
        $this->assertDatabaseCount('market_data_credit_reservations', 3);
    }

    public function test_compare_cache_hit_does_not_reserve_more_credits(): void
    {
        Http::fake(['*' => Http::response($this->profilePayload('BBCA.JK', 'Bank Central Asia'))]);

        $this->get('/bandingkan?symbols=BBCA')->assertOk();
        $this->get('/bandingkan?symbols=BBCA')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('comparison.companies.0.symbol', 'BBCA')
                ->where('comparison.companies.0.status', 'ready')
            );

        Http::assertSentCount(1);
        $this->assertDatabaseCount('market_data_credit_reservations', 1);
    }

    public function test_compare_keeps_other_profiles_when_one_symbol_fails(): void
    {
        Http::fakeSequence()
            ->push($this->profilePayload('BBCA.JK', 'Bank Central Asia'))
            ->push([], 404);

        $this->get('/bandingkan?symbols=BBCA,ZZZZ')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('comparison.symbols', ['BBCA', 'ZZZZ'])
                ->where('comparison.companies.0.symbol', 'BBCA')
                ->where('comparison.companies.0.status', 'ready')
                ->where('comparison.companies.1.symbol', 'ZZZZ')
                ->where('comparison.companies.1.status', 'error')
                ->where('comparison.companies.1.error.reason', 'not_found')
                ->where('comparison.meta.state', 'partial')
            );

        Http::assertSentCount(2);
        $this->assertDatabaseCount('market_data_credit_reservations', 2);
    }

    private function profilePayload(
        string $symbol,
        string $name,
        ?string $sector = 'Financials',
        ?string $subSector = 'Banks',
        ?int $price = 6200,
        ?string $priceDate = '2026-09-23',
    ): array {
        return [
            'symbol' => $symbol,
            'company_name' => $name,
            'overview' => [
                'sector' => $sector,
                'sub_sector' => $subSector,
                'last_close_price' => $price,
                'latest_close_date' => $priceDate,
            ],
        ];
    }
}
