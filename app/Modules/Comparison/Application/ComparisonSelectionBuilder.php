<?php

namespace App\Modules\Comparison\Application;

use App\Modules\MarketData\Application\Contracts\CompanyDirectory;
use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use InvalidArgumentException;

class ComparisonSelectionBuilder
{
    private const LIMIT = 3;

    public function __construct(private readonly CompanyDirectory $directory) {}

    /**
     * @return array{
     *     symbols: list<string>,
     *     companies: list<array<string, mixed>>,
     *     metrics: list<array{label: string, values: array<string, string>, notes: array<string, string>}>,
     *     meta: array{source: string, state: string, limit: int, liveProvider: bool, estimatedCredits: int}
     * }
     */
    public function build(string $userId, ?string $symbols): array
    {
        $requested = $this->parseSymbols($symbols);
        $companies = [];

        foreach ($requested as $symbol) {
            $companies[] = $this->profileCard($userId, $symbol);
        }

        return [
            'symbols' => $requested,
            'companies' => $companies,
            'metrics' => [],
            'meta' => [
                'source' => 'profile',
                'state' => $this->state($companies),
                'limit' => self::LIMIT,
                'liveProvider' => $requested !== [],
                'estimatedCredits' => count($requested),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function parseSymbols(?string $symbols): array
    {
        if ($symbols === null || trim($symbols) === '') {
            return [];
        }

        $parsed = array_values(array_unique(array_filter(array_map(
            fn (string $symbol): string => preg_replace('/\.JK$/i', '', strtoupper(trim($symbol))) ?? '',
            explode(',', $symbols),
        ))));

        foreach ($parsed as $symbol) {
            if (! preg_match('/^[A-Z0-9]{4}$/', $symbol)) {
                throw new InvalidArgumentException("Invalid comparison symbol [{$symbol}].");
            }
        }

        if (count($parsed) > self::LIMIT) {
            throw new InvalidArgumentException('Comparison supports up to 3 symbols.');
        }

        return $parsed;
    }

    /**
     * @return array<string, mixed>
     */
    private function profileCard(string $userId, string $symbol): array
    {
        try {
            $profile = $this->directory->profile($userId, $symbol);

            return [
                'symbol' => $symbol,
                'name' => $this->text($profile['name'] ?? null) ?? $symbol,
                'logoUrl' => $this->text($profile['logoUrl'] ?? null),
                'sector' => $this->text($profile['sector'] ?? null),
                'subSector' => $this->text($profile['subSector'] ?? null),
                'industry' => $this->text($profile['industry'] ?? null),
                'price' => $this->number($profile['price'] ?? null),
                'priceDate' => $this->text($profile['priceDate'] ?? null),
                'fetchedAt' => $this->text($profile['fetchedAt'] ?? null),
                'freshness' => $this->text($profile['fetchedAt'] ?? null) ?? 'Cache aktif',
                'status' => 'ready',
                'error' => null,
            ];
        } catch (MarketDataUnavailable $exception) {
            return [
                'symbol' => $symbol,
                'name' => $symbol,
                'logoUrl' => null,
                'sector' => null,
                'subSector' => null,
                'industry' => null,
                'price' => null,
                'priceDate' => null,
                'fetchedAt' => null,
                'freshness' => 'Tidak tersedia',
                'status' => 'error',
                'error' => $exception->details(),
            ];
        }
    }

    /**
     * @param  list<array<string, mixed>>  $companies
     */
    private function state(array $companies): string
    {
        if ($companies === []) {
            return 'empty';
        }

        foreach ($companies as $company) {
            if (($company['status'] ?? null) === 'error') {
                return 'partial';
            }
        }

        return 'ready';
    }

    private function text(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) && is_finite((float) $value) ? (float) $value : null;
    }
}
