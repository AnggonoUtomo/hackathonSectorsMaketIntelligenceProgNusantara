<?php

namespace App\Modules\MarketData\Infrastructure\Sectors;

use App\Modules\MarketData\Infrastructure\Sectors\Exception\SectorsApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class SectorsApiClient
{
    /**
     * @param  array<string, scalar|null>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        $apiKey = config('services.sectors.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw SectorsApiException::missingApiKey();
        }

        try {
            $response = Http::timeout((int) config('services.sectors.timeout', 10))
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => $apiKey,
                ])
                ->get($this->url($path), $query);
        } catch (ConnectionException $exception) {
            throw SectorsApiException::timeout($exception);
        }

        if ($response->failed()) {
            throw SectorsApiException::providerError($response->status());
        }

        /** @var array<string, mixed> */
        return $response->json() ?? [];
    }

    private function url(string $path): string
    {
        $baseUrl = rtrim((string) config('services.sectors.base_url'), '/');
        $path = trim($path, '/');

        return $baseUrl.'/'.$path.'/';
    }
}
