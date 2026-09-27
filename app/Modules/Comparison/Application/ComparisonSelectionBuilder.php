<?php

namespace App\Modules\Comparison\Application;

use InvalidArgumentException;

class ComparisonSelectionBuilder
{
    private const LIMIT = 3;

    /**
     * @return array{
     *     symbols: list<string>,
     *     companies: list<array{symbol: string, name: string, sector: string, freshness: string}>,
     *     metrics: list<array{label: string, values: array<string, string>, notes: array<string, string>}>,
     *     meta: array{source: string, state: string, limit: int, liveProvider: bool}
     * }
     */
    public function build(?string $symbols): array
    {
        $requested = $this->parseSymbols($symbols);

        return [
            'symbols' => $requested,
            'companies' => [],
            'metrics' => [],
            'meta' => [
                'source' => 'selection',
                'state' => $requested === [] ? 'empty' : 'selected',
                'limit' => self::LIMIT,
                'liveProvider' => false,
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
}
