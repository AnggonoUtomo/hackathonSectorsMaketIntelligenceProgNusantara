<?php

namespace App\Modules\MarketData\Infrastructure\Sectors;

use App\Modules\MarketData\Application\Contracts\CompanyDirectory;
use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use App\Modules\MarketData\Infrastructure\Cache\MarketDataCache;
use App\Modules\MarketData\Infrastructure\Credit\CreditReservationException;
use App\Modules\MarketData\Infrastructure\Credit\CreditReservationService;
use App\Modules\MarketData\Infrastructure\Sectors\Exception\SectorsApiException;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SectorsCompanyDirectory implements CompanyDirectory
{
    public function __construct(
        private readonly SectorsApiClient $client,
        private readonly MarketDataCache $cache,
        private readonly CreditReservationService $credits,
    ) {}

    public function search(string $userId, string $query, int $page, int $perPage): array
    {
        $parameters = ['limit' => $perPage, 'offset' => ($page - 1) * $perPage, 'order_by' => 'symbol'];
        if ($query !== '') {
            // Accept identity text only; never accept a provider expression from the caller.
            if (! preg_match('/\A[\pL\pN .&-]{2,100}\z/u', $query)) {
                throw new MarketDataUnavailable('invalid_query', 422, 'Gunakan nama perusahaan atau kode saham.');
            }
            $parameters['where'] = "company_name like '%{$query}%' or symbol like '%{$query}%'";
        }

        return $this->remember($userId, 'companies', $parameters, function (array $payload) use ($page, $perPage): array {
            if (! isset($payload['results'], $payload['pagination']['total_count']) || ! is_array($payload['results'])
                || ! is_numeric($payload['pagination']['total_count'])) {
                throw $this->invalidPayload();
            }
            $items = [];
            foreach ($payload['results'] as $row) {
                if (! is_array($row)) {
                    throw $this->invalidPayload();
                }
                $items[] = $this->identity($row);
            }

            return ['items' => $items, 'total' => (int) $payload['pagination']['total_count'],
                'page' => $page, 'perPage' => $perPage];
        });
    }

    public function profile(string $userId, string $symbol): array
    {
        if (! preg_match('/\A[A-Z0-9]{4}\z/', $symbol)) {
            throw new MarketDataUnavailable('not_found', 404, 'Perusahaan tidak ditemukan.');
        }

        return $this->remember($userId, 'company/report/'.$symbol, ['sections' => 'overview'], function (array $payload) use ($symbol): array {
            $identity = $this->identity($payload);
            $overview = $payload['overview'] ?? null;
            if ($identity['symbol'] !== $symbol || ! is_array($overview) || $overview === []) {
                throw $this->invalidPayload();
            }

            return $identity + [
                'sector' => $this->text($overview['sector'] ?? null),
                'subSector' => $this->text($overview['sub_sector'] ?? null),
                'industry' => $this->text($overview['industry'] ?? null),
                'board' => $this->text($overview['listing_board'] ?? null),
                'listingDate' => $this->text($overview['listing_date'] ?? null),
                'address' => $this->text($overview['address'] ?? null),
                'website' => $this->website($overview['website'] ?? null),
                'phone' => $this->text($overview['phone'] ?? null),
                'employees' => $this->number($overview['employee_num'] ?? null),
                'marketCap' => $this->number($overview['market_cap'] ?? null),
                'price' => $this->number($overview['last_close_price'] ?? null),
                'priceDate' => $this->text($overview['latest_close_date'] ?? null),
                'changePercent' => isset($overview['daily_close_change']) && is_numeric($overview['daily_close_change'])
                    ? (float) $overview['daily_close_change'] * 100 : null,
                'indices' => array_values(array_filter(is_array($overview['indices'] ?? null) ? $overview['indices'] : [], 'is_string')),
            ];
        });
    }

    private function remember(string $userId, string $endpoint, array $parameters, Closure $map): array
    {
        // Cache mapped results with their original fetch time; identical in-flight calls share one fetch.
        $key = $this->cache->key('directory-v1/'.$endpoint, $parameters);
        if (is_array($cached = Cache::get($key))) {
            return $cached;
        }

        try {
            return Cache::lock($key.':lock', 30)->block(12, function () use ($key, $userId, $endpoint, $parameters, $map): array {
                return Cache::remember($key, 3600, function () use ($userId, $endpoint, $parameters, $map): array {
                    $this->credits->reserve($userId, $endpoint, 1, 'directory-'.Str::ulid());

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

    private function identity(array $row): array
    {
        $symbol = strtoupper(preg_replace('/\.JK$/i', '', $this->text($row['symbol'] ?? null) ?? '') ?? '');
        $name = $this->text($row['company_name'] ?? null);
        if (! preg_match('/\A[A-Z0-9]{4}\z/', $symbol) || $name === null) {
            throw $this->invalidPayload();
        }

        return ['symbol' => $symbol, 'name' => $name,
            'logoUrl' => 'https://storage.googleapis.com/sectorsapp-sea/logo/'.$symbol.'.webp'];
    }

    private function text(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) && is_finite((float) $value) ? (float) $value : null;
    }

    private function website(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }
        $url = str_starts_with($value, 'www.') ? 'https://'.$value : $value;

        return filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) ? $url : null;
    }

    private function invalidPayload(): MarketDataUnavailable
    {
        return new MarketDataUnavailable('invalid_response', 502, 'Format data perusahaan belum dapat dibaca. Silakan coba lagi.');
    }
}
