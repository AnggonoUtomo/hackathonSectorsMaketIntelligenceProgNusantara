<?php

namespace Tests\Feature\Intelligence;

use App\Models\User;
use App\Modules\Comparison\Application\Contracts\ComparisonSnapshotStore;
use App\Modules\Intelligence\Application\Contracts\ScoreEvidence;
use App\Modules\Intelligence\Domain\ResearchPriorityCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ResearchPriorityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sectors.api_key' => 'test-key', 'services.sectors.base_url' => 'https://provider.test/v2']);
        Http::preventStrayRequests();
        $this->travelTo(now()->setDate(2026, 10, 2)->startOfDay());
    }

    public function test_real_adapter_complete_peers_cache_and_immutable_replay(): void
    {
        $this->fake();
        $this->actingAs(User::factory()->create());
        $result = $this->getJson('/nusalens/companies/TARG/score')->assertOk()->assertJsonPath('completeness', 65)->assertJsonPath('score', null)->json();
        Http::assertSentCount(2);
        $this->assertDatabaseCount('market_data_credit_reservations', 2);
        $this->travel(1)->hours();
        $this->getJson('/nusalens/companies/TARG/score')->assertJsonPath('evidenceId', $result['evidenceId'])->assertJsonPath('fetchedAt', $result['fetchedAt']);
        Http::assertSentCount(2);
        $this->assertDatabaseCount('intelligence_evidence', 1);
        $evidence = $this->getJson('/nusalens/evidence/'.$result['evidenceId'])->assertOk()->json();
        $replayed = (new ResearchPriorityCalculator)->calculate('TARG', $evidence['input']['companies']);
        $this->assertEquals($result['components'], $replayed['components']);
        $this->assertCount(5, $result['components'][0]['metrics'][0]['peers']);
        $this->travel(25)->hours();
        $fresh = $this->getJson('/nusalens/companies/TARG/score')->assertOk()->json();
        $this->assertNotSame($result['evidenceId'], $fresh['evidenceId']);
        $this->assertDatabaseCount('intelligence_evidence', 2);
        $this->assertEquals($evidence, app(ScoreEvidence::class)->find($result['evidenceId']));
    }

    public function test_partial_population_and_quota_do_not_create_a_score(): void
    {
        $this->fake(partial: true);
        $this->actingAs(User::factory()->create());
        $this->getJson('/nusalens/companies/TARG/score')->assertStatus(502)->assertJsonPath('reason', 'incomplete_population');
        $this->assertDatabaseCount('intelligence_evidence', 0);
    }

    public function test_credit_limit_does_not_degrade_to_empty_success(): void
    {
        config(['marketdata.credits.daily_user_quota' => 1]);
        $this->fake();
        $this->actingAs(User::factory()->create())->getJson('/nusalens/companies/TARG/score')->assertStatus(429)->assertJsonPath('reason', 'credit_limit');
        Http::assertSentCount(1);
        $this->assertDatabaseCount('intelligence_evidence', 0);
    }

    public function test_guest_and_unverified_requests_make_no_provider_calls(): void
    {
        $this->getJson('/nusalens/companies/TARG/score')->assertUnauthorized();
        $this->actingAs(User::factory()->unverified()->create())->getJson('/nusalens/companies/TARG/score')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_valuation_uses_matching_price_dates_and_explicit_ttm_with_full_peer_group(): void
    {
        $this->fake();
        $this->actingAs(User::factory()->create());
        $result = $this->getJson('/nusalens/companies/TARG/score?include_market=1')->assertOk()->json();
        $this->assertEquals(85, $result['completeness']);
        $this->assertEquals(50, $result['score']);
        $this->assertSame('Q2-2026', $result['components'][2]['metrics'][0]['period']);
        $this->assertEquals(100 / 42, $result['components'][2]['metrics'][0]['value']);
        $this->assertSame('2026-10-02', $result['components'][2]['metrics'][0]['priceDate']);
        Http::assertSentCount(8);
        $this->assertDatabaseCount('market_data_credit_reservations', 8);
    }

    public function test_valuation_preflight_does_not_spend_on_a_partial_peer_population(): void
    {
        config(['marketdata.credits.daily_user_quota' => 7]);
        $this->fake();
        $this->actingAs(User::factory()->create())->getJson('/nusalens/companies/TARG/score?include_market=1')
            ->assertOk()->assertJsonPath('completeness', 65)->assertJsonPath('score', null)
            ->assertJsonPath('marketNotice', fn ($message) => str_contains($message, '6 credit'));
        Http::assertSentCount(2);
    }

    public function test_candidates_reuse_evidence_without_http_and_keep_url_filters(): void
    {
        $this->fake();
        $this->actingAs(User::factory()->create());
        $id = $this->getJson('/nusalens/companies/TARG/score')->assertOk()->json('evidenceId');
        $this->get('/kandidat-menarik?evidence='.$id.'&component=growth&q=AAA')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('nusalens/peer-candidates')
                ->where('result.total', 5)->has('result.items', 5)->where('result.items.0.symbol', 'AAA1')
                ->where('filters.component', 'growth')->where('filters.q', 'AAA'));
        $this->get('/kandidat-menarik?evidence='.$id.'&page=2')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->has('result.items', 0));
        $this->get('/kandidat-menarik')->assertRedirect('/temukan-saham');
        Http::assertSentCount(2);
    }

    public function test_market_observation_age_caps_evidence_expiry_and_old_quotes_are_not_ranked(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-02 23:30:00', 'Asia/Jakarta'));
        $this->fake();
        $this->actingAs(User::factory()->create());
        $result = $this->getJson('/nusalens/companies/TARG/score?include_market=1')->assertOk()->json();
        $this->assertEquals(85, $result['completeness']);
        $this->assertSame('2026-10-02T17:00:00+00:00', $result['expiresAt']);
        $this->travel(31)->minutes();
        $this->getJson('/nusalens/companies/TARG/score?include_market=1')->assertOk()
            ->assertJsonPath('completeness', 65)->assertJsonPath('score', null);
    }

    public function test_malformed_provider_identity_returns_safe_error_without_evidence(): void
    {
        Http::fake(['*' => Http::response(['results' => [['symbol' => ['invalid'], 'query_values' => []]],
            'pagination' => ['total_count' => 1]])]);
        $this->actingAs(User::factory()->create())->getJson('/nusalens/companies/TARG/score')
            ->assertStatus(502)->assertJsonPath('reason', 'incomplete_population');
        $this->assertDatabaseCount('intelligence_evidence', 0);
    }

    public function test_retention_preserves_recent_evidence_and_complete_saved_copy(): void
    {
        $this->fake();
        $user = User::factory()->create();
        $this->actingAs($user);
        $result = $this->getJson('/nusalens/companies/TARG/score')->assertOk()->json();
        $record = app(ScoreEvidence::class)->find($result['evidenceId']);
        $snapshot = app(ComparisonSnapshotStore::class)
            ->create($user->id, 'Riwayat', ['TARG'], ['evidence' => ['TARG' => $record]], null);
        $this->travel(31)->days();
        $fresh = $this->getJson('/nusalens/companies/TARG/score')->assertOk()->json();
        $this->artisan('nusalens:prune-evidence')->expectsOutput('Bukti kedaluwarsa dibersihkan: 1')->assertSuccessful();
        $this->assertNull(app(ScoreEvidence::class)->find($result['evidenceId']));
        $this->assertNotNull(app(ScoreEvidence::class)->find($fresh['evidenceId']));
        $saved = app(ComparisonSnapshotStore::class)->findForUser($user->id, $snapshot['id']);
        $this->assertEquals($record, $saved['payload']['evidence']['TARG']);
    }

    private function fake(bool $partial = false): void
    {
        $rows = [];
        foreach (['TARG', 'AAA1', 'AAA2', 'AAA3', 'AAA4', 'AAA5'] as $symbol) {
            $rows[] = ['symbol' => $symbol.'.JK', 'company_name' => $symbol.' company', 'query_values' => [
                'sector' => 'Energy', 'sub_sector' => 'Oil, Gas & Coal', 'industry' => 'Coal', 'sub_industry' => 'Coal Production',
                'roe[2025]' => 0.2, 'roa[2025]' => 0.1, 'total_equity[2025]' => 100, 'total_assets[2025]' => 200,
                'total_debt[2025]' => 20, 'current_assets[2025]' => 50, 'current_liabilities[2025]' => 10,
                'revenue_q[Q2-2026]' => 120, 'earnings_q[Q2-2026]' => 12,
                'earnings_q[Q1-2026]' => 10, 'earnings_q[Q4-2025]' => 10, 'earnings_q[Q3-2025]' => 10,
                'total_equity_q[Q2-2026]' => 50,
                'revenue_q[Q2-2025]' => 100, 'earnings_q[Q2-2025]' => 10]];
        }
        Http::fake(function ($request) use ($rows, $partial) {
            if (str_contains($request->url(), '/daily/')) {
                preg_match('~/daily/([A-Z0-9]{4})/~', $request->url(), $match);

                return Http::response([['symbol' => $match[1].'.JK', 'date' => '2026-10-02', 'close' => 1, 'market_cap' => 100]]);
            }
            $single = str_starts_with($request['where'], 'symbol');
            $items = $single ? [$rows[0]] : (($request['offset'] ?? 0) > 0 ? [] : $rows);

            return Http::response(['results' => $items, 'pagination' => ['total_count' => $single ? 1 : ($partial ? 7 : 6)]]);
        });
    }
}
