<?php

namespace Tests\Feature\Comparison;

use App\Models\User;
use App\Modules\Company\Application\Contracts\FactAttestation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ComparisonSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['inertia.ssr.enabled' => false]);
    }

    public function test_verified_user_can_save_and_view_private_comparison_snapshot(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/bandingkan?symbols=BBCA,TLKM')
            ->post('/bandingkan/snapshots', $this->payload());

        $response->assertRedirect();
        $snapshotId = (string) str($response->headers->get('Location'))->afterLast('/');

        $this->assertDatabaseHas('comparison_snapshots', [
            'id' => $snapshotId,
            'user_id' => $user->id,
            'version' => 1,
        ]);

        $this->get('/bandingkan/snapshots/'.$snapshotId)->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/comparison-snapshot-detail')
                ->where('snapshot.id', $snapshotId)
                ->where('snapshot.symbols', ['BBCA', 'TLKM'])
                ->where('snapshot.payload.sections.prices.BBCA.rows.0.close', 6200)
            );
    }

    public function test_snapshot_index_only_lists_owner_snapshots(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($owner)->post('/bandingkan/snapshots', $this->payload(['title' => 'Milik saya']));
        $this->actingAs($other)->post('/bandingkan/snapshots', $this->payload(['title' => 'Milik user lain']));

        $this->actingAs($owner)->get('/bandingkan/snapshots')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('nusalens/comparison-snapshots')
                ->has('snapshots', 1)
                ->where('snapshots.0.title', 'Milik saya')
            );
    }

    public function test_users_cannot_open_other_users_snapshot(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($owner)->post('/bandingkan/snapshots', $this->payload());
        $snapshotId = (string) str($response->headers->get('Location'))->afterLast('/');

        $this->actingAs($other)->get('/bandingkan/snapshots/'.$snapshotId)->assertNotFound();
    }

    public function test_saved_research_is_paginated_and_searchable_without_other_users_data(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        for ($i = 1; $i <= 16; $i++) {
            $this->post('/bandingkan/snapshots', $this->payload(['title' => 'Riset '.$i]))->assertRedirect();
        }
        $this->get('/bandingkan/snapshots')->assertInertia(fn (AssertableInertia $page) => $page
            ->has('snapshots', 15)->where('pagination.total', 16)->where('pagination.lastPage', 2));
        $this->get('/bandingkan/snapshots?page=2')->assertInertia(fn (AssertableInertia $page) => $page->has('snapshots', 1));
        $this->get('/bandingkan/snapshots?q=Riset%2016')->assertInertia(fn (AssertableInertia $page) => $page
            ->has('snapshots', 1)->where('snapshots.0.title', 'Riset 16'));
    }

    public function test_updating_snapshot_creates_new_version_without_overwriting_previous(): void
    {
        $user = User::factory()->create();

        $first = $this->actingAs($user)->post('/bandingkan/snapshots', $this->payload());
        $firstId = (string) str($first->headers->get('Location'))->afterLast('/');

        $second = $this->post('/bandingkan/snapshots', $this->payload([
            'title' => 'Versi baru',
            'base_snapshot_id' => $firstId,
        ]));
        $secondId = (string) str($second->headers->get('Location'))->afterLast('/');

        $this->assertNotSame($firstId, $secondId);
        $this->assertDatabaseHas('comparison_snapshots', [
            'id' => $firstId,
            'version' => 1,
            'created_from_snapshot_id' => null,
        ]);
        $this->assertDatabaseHas('comparison_snapshots', [
            'id' => $secondId,
            'version' => 2,
            'created_from_snapshot_id' => $firstId,
        ]);
    }

    public function test_snapshot_validation_rejects_more_than_three_symbols(): void
    {
        $this->actingAs(User::factory()->create())
            ->from('/bandingkan')
            ->post('/bandingkan/snapshots', $this->payload([
                'symbols' => ['BBCA', 'TLKM', 'ICBP', 'BBRI'],
            ]))
            ->assertRedirect('/bandingkan')
            ->assertSessionHasErrors('symbols');
    }

    private function payload(array $overrides = []): array
    {
        $payload = array_replace_recursive([
            'title' => 'Bank dan Telko',
            'symbols' => ['BBCA', 'TLKM'],
            'payload' => [
                'companies' => [
                    ['symbol' => 'BBCA', 'name' => 'Bank Central Asia', 'status' => 'ready', 'fetchedAt' => '2026-09-23T10:00:00+07:00'],
                    ['symbol' => 'TLKM', 'name' => 'Telkom Indonesia', 'status' => 'ready', 'fetchedAt' => '2026-09-23T10:00:00+07:00'],
                ],
                'sections' => [
                    'prices' => [
                        'BBCA' => [
                            'symbol' => 'BBCA',
                            'section' => 'prices',
                            'fetchedAt' => '2026-09-23T10:00:00+07:00',
                            'rows' => [['date' => '2026-09-23', 'close' => 6200]],
                        ],
                    ],
                ],
            ],
        ], $overrides);
        $attestation = app(FactAttestation::class);
        foreach ($payload['payload']['companies'] as &$company) {
            $company['receipt'] = $attestation->seal($company, 'profile');
        }
        unset($company);
        foreach ($payload['payload']['sections'] as $section => &$rows) {
            foreach ($rows as &$entry) {
                $entry['receipt'] = $attestation->seal($entry, $section);
            }
            unset($entry);
        }
        unset($rows);

        return $payload;
    }

    public function test_tampered_browser_numbers_are_ignored_and_missing_receipt_is_rejected(): void
    {
        $payload = $this->payload();
        $payload['payload']['sections']['prices']['BBCA']['rows'][0]['close'] = 1;
        $response = $this->actingAs(User::factory()->create())->post('/bandingkan/snapshots', $payload)->assertRedirect();
        $id = (string) str($response->headers->get('Location'))->afterLast('/');
        $this->get('/bandingkan/snapshots/'.$id)->assertInertia(fn (AssertableInertia $p) => $p->where('snapshot.payload.sections.prices.BBCA.rows.0.close', 6200));
        unset($payload['payload']['companies'][0]['receipt']);
        $this->post('/bandingkan/snapshots', $payload)->assertSessionHasErrors('payload.companies.0.receipt');
    }

    public function test_rename_delete_and_base_version_enforce_ownership(): void
    {
        $owner = User::factory()->create();
        $response = $this->actingAs($owner)->post('/bandingkan/snapshots', $this->payload());
        $id = (string) str($response->headers->get('Location'))->afterLast('/');
        $this->actingAs(User::factory()->create())->patch('/bandingkan/snapshots/'.$id, ['title' => 'Stolen'])->assertNotFound();
        $this->delete('/bandingkan/snapshots/'.$id)->assertNotFound();
        $this->post('/bandingkan/snapshots', $this->payload(['base_snapshot_id' => $id]))->assertSessionHasErrors('payload');
        $this->actingAs($owner)->patch('/bandingkan/snapshots/'.$id, ['title' => 'Riset bank'])->assertRedirect();
        $this->assertDatabaseHas('comparison_snapshots', ['id' => $id, 'title' => 'Riset bank', 'version' => 1]);
        $this->delete('/bandingkan/snapshots/'.$id)->assertRedirect('/bandingkan/snapshots');
        $this->assertDatabaseMissing('comparison_snapshots', ['id' => $id]);
    }
}
