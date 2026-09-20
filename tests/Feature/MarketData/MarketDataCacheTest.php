<?php

namespace Tests\Feature\MarketData;

use App\Models\User;
use App\Modules\MarketData\Infrastructure\Cache\MarketDataCache;
use App\Modules\MarketData\Infrastructure\Credit\CreditReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MarketDataCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_miss_resolves_value_and_reserves_credit_once(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $calls = 0;

        $value = app(MarketDataCache::class)->remember(
            endpoint: 'companies',
            parameters: ['keyword' => 'BBCA', 'page' => 1],
            ttlSeconds: 3600,
            resolver: function () use ($user, &$calls): array {
                $calls++;

                app(CreditReservationService::class)->reserve(
                    userId: $user->id,
                    endpoint: 'companies',
                    estimatedCredits: 1,
                    correlationId: 'corr-cache-miss-1',
                );

                return ['symbols' => ['BBCA']];
            },
        );

        $this->assertSame(['symbols' => ['BBCA']], $value);
        $this->assertSame(1, $calls);
        $this->assertDatabaseCount('market_data_credit_reservations', 1);
    }

    public function test_cache_hit_does_not_resolve_or_reserve_credit(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $cache = app(MarketDataCache::class);

        $cache->remember(
            endpoint: 'companies',
            parameters: ['keyword' => 'BBCA', 'page' => 1],
            ttlSeconds: 3600,
            resolver: function () use ($user): array {
                app(CreditReservationService::class)->reserve(
                    userId: $user->id,
                    endpoint: 'companies',
                    estimatedCredits: 1,
                    correlationId: 'corr-cache-hit-1',
                );

                return ['symbols' => ['BBCA']];
            },
        );

        $value = $cache->remember(
            endpoint: 'companies',
            parameters: ['page' => 1, 'keyword' => 'BBCA'],
            ttlSeconds: 3600,
            resolver: function (): array {
                $this->fail('Cache hit should not resolve upstream data.');
            },
        );

        $this->assertSame(['symbols' => ['BBCA']], $value);
        $this->assertDatabaseCount('market_data_credit_reservations', 1);
    }

    public function test_cache_key_includes_mapping_version(): void
    {
        Cache::flush();

        $cache = app(MarketDataCache::class);

        $cache->remember('companies', ['keyword' => 'BBCA'], 3600, fn (): array => ['version' => 1]);

        config(['marketdata.cache.mapping_version' => 'v2']);

        $value = $cache->remember('companies', ['keyword' => 'BBCA'], 3600, fn (): array => ['version' => 2]);

        $this->assertSame(['version' => 2], $value);
    }

    public function test_cache_key_is_deterministic_and_parameter_sensitive(): void
    {
        config([
            'marketdata.cache.prefix' => 'nusalens-test',
            'marketdata.cache.mapping_version' => 'v1',
        ]);

        $cache = app(MarketDataCache::class);

        $first = $cache->key('companies', ['keyword' => 'BBCA', 'page' => 1]);
        $same = $cache->key('/companies/', ['page' => 1, 'keyword' => 'BBCA']);
        $different = $cache->key('companies', ['keyword' => 'BBRI', 'page' => 1]);

        $this->assertSame($first, $same);
        $this->assertNotSame($first, $different);
        $this->assertStringStartsWith('nusalens-test:sectors:', $first);
        $this->assertStringNotContainsString('BBCA', $first);
    }

    public function test_expired_cache_resolves_again(): void
    {
        Cache::flush();

        $cache = app(MarketDataCache::class);
        $calls = 0;

        $cache->remember('companies', ['keyword' => 'BBCA'], 1, function () use (&$calls): array {
            $calls++;

            return ['call' => $calls];
        });

        $this->travel(2)->seconds();

        $value = $cache->remember('companies', ['keyword' => 'BBCA'], 1, function () use (&$calls): array {
            $calls++;

            return ['call' => $calls];
        });

        $this->assertSame(['call' => 2], $value);
    }
}
