<?php

namespace Tests\Feature\Comparison;

use App\Models\User;
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
        return array_replace_recursive([
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
    }
}
