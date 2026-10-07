<?php

namespace Tests\Feature\Intelligence;

use App\Models\User;
use App\Modules\Intelligence\Application\Contracts\ScoreEvidence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class EvidencePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['inertia.ssr.enabled' => false]);
        Http::preventStrayRequests();
    }

    public function test_browser_gets_a_readable_page_and_json_contract_stays_unchanged(): void
    {
        $record = $this->record();
        $this->mock(ScoreEvidence::class)->shouldReceive('find')->twice()->with($record['id'])->andReturn($record);
        $this->actingAs(User::factory()->create());
        $url = '/nusalens/evidence/'.$record['id'];
        $this->get($url)->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('nusalens/research-evidence')->where('evidence', $record));
        $response = $this->getJson($url)->assertOk()->assertExactJson($record);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        Http::assertNothingSent();
        $this->assertDatabaseCount('market_data_credit_reservations', 0);
    }

    public function test_inertia_navigation_also_receives_a_page_instead_of_raw_evidence(): void
    {
        $record = $this->record();
        $this->mock(ScoreEvidence::class)->shouldReceive('find')->twice()->andReturn($record);
        $this->actingAs(User::factory()->create());
        $url = '/nusalens/evidence/'.$record['id'];
        $version = $this->get($url)->assertOk()->viewData('page')['version'];
        $this->get($url, [
            'X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'text/html, application/xhtml+xml',
            'X-Inertia-Version' => (string) $version,
        ])->assertOk()->assertHeader('X-Inertia', 'true')->assertJsonPath('component', 'nusalens/research-evidence')
            ->assertJsonPath('props.evidence.result.formulaVersion', 'nusalens-v1.0.0');
        Http::assertNothingSent();
    }

    public function test_missing_evidence_has_a_page_for_browsers_and_404_for_json(): void
    {
        $id = (string) Str::ulid();
        $this->mock(ScoreEvidence::class)->shouldReceive('find')->twice()->with($id)->andReturnNull();
        $this->actingAs(User::factory()->create());
        $this->get('/nusalens/evidence/'.$id)->assertNotFound()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('nusalens/research-evidence')->where('evidence', null));
        $this->getJson('/nusalens/evidence/'.$id)->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_guests_and_unverified_accounts_cannot_read_evidence(): void
    {
        $this->mock(ScoreEvidence::class)->shouldNotReceive('find');
        $url = '/nusalens/evidence/'.Str::ulid();
        $this->get($url)->assertRedirect('/login');
        $this->getJson($url)->assertUnauthorized();
        $this->actingAs(User::factory()->unverified()->create());
        $this->get($url)->assertRedirect('/verify-email');
        $this->getJson($url)->assertForbidden();
        Http::assertNothingSent();
    }

    private function record(): array
    {
        return ['id' => (string) Str::ulid(), 'input' => ['symbol' => 'AADI', 'companies' => [], 'source' => 'Sectors Financial API v2'],
            'result' => ['symbol' => 'AADI', 'name' => 'Adaro Andalan Indonesia Tbk', 'formulaVersion' => 'nusalens-v1.0.0',
                'score' => null, 'completeness' => 65, 'components' => [], 'fetchedAt' => '2026-10-01T00:00:00Z',
                'expiresAt' => '2026-10-02T00:00:00Z', 'reason' => 'Kelengkapan minimal 70%.', 'populationCount' => 6]];
    }
}
