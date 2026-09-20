<?php

namespace App\Modules\MarketData\Infrastructure\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

class MarketDataCache
{
    /**
     * @param  array<string, mixed>  $parameters
     * @param  Closure(): array<string, mixed>  $resolver
     * @return array<string, mixed>
     */
    public function remember(string $endpoint, array $parameters, int $ttlSeconds, Closure $resolver): array
    {
        $key = $this->key($endpoint, $parameters);

        /** @var array<string, mixed> */
        return Cache::remember($key, $ttlSeconds, $resolver);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function key(string $endpoint, array $parameters): string
    {
        ksort($parameters);

        $payload = [
            'endpoint' => trim($endpoint, '/'),
            'mapping_version' => (string) config('marketdata.cache.mapping_version', 'v1'),
            'parameters' => $parameters,
        ];

        return implode(':', [
            (string) config('marketdata.cache.prefix', 'marketdata'),
            'sectors',
            hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)),
        ]);
    }
}
