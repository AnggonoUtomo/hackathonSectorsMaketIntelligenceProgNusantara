<?php

namespace App\Modules\MarketData\Infrastructure\Sectors;

use App\Modules\MarketData\Application\DTO\ScreenerCompany;
use App\Modules\MarketData\Application\DTO\ScreenerResult;
use App\Modules\MarketData\Application\DTO\StructuredScreenerCriteria;
use App\Modules\MarketData\Infrastructure\Cache\MarketDataCache;
use App\Modules\MarketData\Infrastructure\Credit\CreditReservationService;

class StructuredCompanyScreener
{
    private const ENDPOINT = 'companies';

    public function __construct(
        private readonly SectorsApiClient $client,
        private readonly MarketDataCache $cache,
        private readonly CreditReservationService $credits,
    ) {}

    public function screen(string $userId, StructuredScreenerCriteria $criteria, string $correlationId): ScreenerResult
    {
        $payload = $this->cache->remember(
            endpoint: self::ENDPOINT,
            parameters: $criteria->toQueryParameters(),
            ttlSeconds: 3600,
            resolver: function () use ($userId, $criteria, $correlationId): array {
                $this->credits->reserve(
                    userId: $userId,
                    endpoint: self::ENDPOINT,
                    estimatedCredits: 1,
                    correlationId: $correlationId,
                );

                return $this->client->get(self::ENDPOINT, $criteria->toQueryParameters());
            },
        );

        return $this->mapResult($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapResult(array $payload): ScreenerResult
    {
        $items = $payload['results'] ?? $payload['data'] ?? [];
        $meta = $payload['pagination'] ?? $payload['meta'] ?? [];

        if (! is_array($items)) {
            $items = [];
        }

        if (! is_array($meta)) {
            $meta = [];
        }

        $companies = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $symbol = (string) ($item['symbol'] ?? '');
            $name = (string) ($item['company_name'] ?? $item['name'] ?? '');

            if ($symbol === '' || $name === '') {
                continue;
            }

            $queryValues = $item['query_values'] ?? [];

            $companies[] = new ScreenerCompany(
                symbol: $symbol,
                name: $name,
                sector: $this->optionalString($item['sector'] ?? null),
                subSector: $this->optionalString($item['sub_sector'] ?? $item['subsector'] ?? null),
                queryValues: is_array($queryValues) ? $queryValues : [],
            );
        }

        return new ScreenerResult(
            companies: $companies,
            page: $this->pageFromMeta($meta, $payload),
            perPage: (int) ($meta['limit'] ?? $meta['per_page'] ?? $payload['per_page'] ?? count($companies)),
            total: (int) ($meta['total_count'] ?? $meta['total'] ?? count($companies)),
            lastPage: $this->lastPageFromMeta($meta, count($companies)),
        );
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $payload
     */
    private function pageFromMeta(array $meta, array $payload): int
    {
        if (isset($meta['offset'], $meta['limit']) && (int) $meta['limit'] > 0) {
            return (int) floor((int) $meta['offset'] / (int) $meta['limit']) + 1;
        }

        return (int) ($meta['current_page'] ?? $payload['page'] ?? 1);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function lastPageFromMeta(array $meta, int $fallbackCount): int
    {
        if (isset($meta['total_count'], $meta['limit']) && (int) $meta['limit'] > 0) {
            return (int) ceil((int) $meta['total_count'] / (int) $meta['limit']);
        }

        return (int) ($meta['last_page'] ?? $meta['total_pages'] ?? 1);
    }

    private function optionalString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = (string) $value;

        return $value === '' ? null : $value;
    }
}
