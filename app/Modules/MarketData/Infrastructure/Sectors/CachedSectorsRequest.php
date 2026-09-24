<?php

namespace App\Modules\MarketData\Infrastructure\Sectors;

use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use App\Modules\MarketData\Infrastructure\Cache\MarketDataCache;
use App\Modules\MarketData\Infrastructure\Credit\CreditReservationException;
use App\Modules\MarketData\Infrastructure\Credit\CreditReservationService;
use App\Modules\MarketData\Infrastructure\Sectors\Exception\SectorsApiException;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CachedSectorsRequest
{
    public function __construct(
        private readonly SectorsApiClient $client,
        private readonly MarketDataCache $cache,
        private readonly CreditReservationService $credits,
    ) {}

    public function remember(string $userId, string $endpoint, array $parameters, Closure $map, int $cost = 1, int $ttl = 3600, string $namespace = 'directory-v1'): array
    {
        // Cache mapped results with their original fetch time; identical in-flight calls share one fetch.
        $key = $this->cache->key($namespace.'/'.$endpoint, $parameters);
        if (is_array($cached = Cache::get($key))) {
            return $cached;
        }

        try {
            return Cache::lock($key.':lock', 30)->block(12, function () use ($key, $userId, $endpoint, $parameters, $map, $cost, $ttl): array {
                return Cache::remember($key, $ttl, function () use ($userId, $endpoint, $parameters, $map, $cost): array {
                    $this->credits->reserve($userId, $endpoint, $cost, 'request-'.Str::ulid());

                    return $map($this->client->get($endpoint, $parameters)) + ['fetchedAt' => now()->toIso8601String()];
                });
            });
        } catch (CreditReservationException) {
            throw new MarketDataUnavailable('credit_limit', 429, 'Batas penggunaan data tercapai. Data tersimpan tetap dapat dibuka.');
        } catch (LockTimeoutException) {
            throw new MarketDataUnavailable('busy', 503, 'Data sedang dimuat. Silakan coba lagi.');
        } catch (SectorsApiException $exception) {
            if ($exception->providerStatus() === 404) {
                throw new MarketDataUnavailable('not_found', 404, 'Perusahaan tidak ditemukan di sumber data.');
            }
            throw new MarketDataUnavailable('provider_unavailable', $exception->providerStatus() === 429 ? 429 : 502,
                'Data Sectors belum dapat dimuat. Silakan coba lagi.');
        }
    }
}
