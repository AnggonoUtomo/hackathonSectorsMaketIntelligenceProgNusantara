<?php

namespace App\Modules\MarketData\Infrastructure\Sectors;

use App\Modules\MarketData\Application\Contracts\PeerFinancialData;
use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use Carbon\CarbonImmutable;

final class SectorsPeerFinancialData implements PeerFinancialData
{
    private const ANNUAL = ['roe' => 'roe', 'roa' => 'roa', 'total_equity' => 'equity', 'total_assets' => 'assets',
        'total_debt' => 'debt', 'current_assets' => 'currentAssets', 'current_liabilities' => 'currentLiabilities',
        'total_capital' => 'capital', 'total_risk_weighted_asset' => 'riskWeightedAssets',
        'non_performing_loan' => 'nonPerformingLoan', 'gross_loan' => 'grossLoan'];

    public function __construct(private readonly CachedSectorsRequest $requests) {}

    public function fetch(string $userId, string $symbol): array
    {
        if (! preg_match('/\A[A-Z0-9]{4}\z/', $symbol)) {
            throw $this->invalid();
        }
        $date = now('Asia/Jakarta');
        $year = (int) $date->year - 1;
        $quarter = $date->copy()->startOfQuarter()->subQuarter();
        $periods = ['annual' => [(string) $year, (string) ($year - 1)],
            'quarterly' => ['Q'.$quarter->quarter.'-'.$quarter->year, 'Q'.$quarter->copy()->subQuarter()->quarter.'-'.$quarter->copy()->subQuarter()->year]];
        $fields = ['symbol', 'sector', 'sub_sector', 'industry', 'sub_industry'];
        foreach ($periods['annual'] as $annual) {
            foreach (array_keys(self::ANNUAL) as $field) {
                $fields[] = $field.'['.$annual.']';
            }
        }
        $quarters = [];
        for ($i = 0; $i < 6; $i++) {
            $period = 'Q'.$quarter->quarter.'-'.$quarter->year;
            $quarters[] = $period;
            foreach (['revenue_q', 'earnings_q', 'total_equity_q'] as $field) {
                $fields[] = $field.'['.$period.']';
            }
            $quarter->subQuarter();
        }
        $parameters = ['order_by' => implode(',', $fields), 'include_query_values' => 'true', 'limit' => 200];
        $targetPage = $this->page($userId, $parameters + ['where' => "symbol = '{$symbol}.JK'", 'offset' => 0]);
        $target = $targetPage['results'][0] ?? null;
        if (! is_array($target)) {
            throw new MarketDataUnavailable('not_found', 404, 'Perusahaan tidak ditemukan pada sumber scoring.');
        }
        if (strtoupper(preg_replace('/\.JK$/i', '', $target['symbol'])) !== $symbol || $targetPage['pagination']['total_count'] !== 1) {
            throw $this->invalid();
        }
        $sector = $target['query_values']['sector'] ?? null;
        if (! is_string($sector) || ! preg_match('/\A[\pL\pN &.,()\/-]{1,100}\z/u', $sector)) {
            throw $this->invalid();
        }
        $companies = [];
        $expected = null;
        $offset = 0;
        $times = [];
        do {
            $page = $this->page($userId, $parameters + ['where' => "sector = '{$sector}'", 'offset' => $offset]);
            $total = $page['pagination']['total_count'];
            if (($expected !== null && $total !== $expected) || $total > 2000) {
                throw $this->invalid();
            }
            $expected = $total;
            $times[] = $page['fetchedAt'];
            foreach ($page['results'] as $row) {
                $code = strtoupper(preg_replace('/\.JK$/i', '', $row['symbol'] ?? ''));
                if (! preg_match('/\A[A-Z0-9]{4}\z/', $code) || isset($companies[$code]) || ! is_string($row['company_name'] ?? null)) {
                    throw $this->invalid();
                }
                $values = $row['query_values'] ?? [];
                if (($values['sector'] ?? null) !== $sector) {
                    throw $this->invalid();
                }
                $annualData = [];
                foreach ($periods['annual'] as $annual) {
                    foreach (self::ANNUAL as $field => $internal) {
                        $annualData[$annual][$internal] = $this->number($values[$field.'['.$annual.']'] ?? null);
                    }
                }
                $quarterlyData = [];
                foreach ($quarters as $period) {
                    foreach (['revenue_q' => 'revenue', 'earnings_q' => 'earnings', 'total_equity_q' => 'equity'] as $field => $internal) {
                        $quarterlyData[$period][$internal] = $this->number($values[$field.'['.$period.']'] ?? null);
                    }
                }
                $subsector = $values['sub_sector'] ?? null;
                $companies[$code] = ['symbol' => $code, 'name' => $row['company_name'],
                    'kind' => strtolower((string) $subsector) === 'banks' ? 'bank' : (strtolower($sector) === 'financials' ? 'financial' : 'nonfinancial'),
                    'groups' => ['sector' => $sector, 'subsector' => $subsector, 'industry' => $values['industry'] ?? null, 'subindustry' => $values['sub_industry'] ?? null],
                    'annual' => $annualData, 'quarterly' => $quarterlyData, 'periods' => $periods, 'fetchedAt' => $page['fetchedAt']];
            }
            $count = count($page['results']);
            $offset += $count;
            if ($count === 0 && $offset < $expected) {
                throw $this->invalid();
            }
        } while ($offset < $expected);
        if (count($companies) !== $expected || ! isset($companies[$symbol])) {
            throw $this->invalid();
        }
        ksort($companies);
        sort($times);

        return ['symbol' => $symbol, 'companies' => array_values($companies), 'fetchedAt' => $times[0],
            'expiresAt' => CarbonImmutable::parse($times[0])->addDay()->toIso8601String(),
            'source' => 'Sectors Financial API v2', 'mappingVersion' => 'peer-fundamentals-v1', 'completePopulation' => true];
    }

    private function page(string $userId, array $parameters): array
    {
        return $this->requests->remember($userId, 'companies', $parameters, function (array $payload): array {
            if (! is_array($payload['results'] ?? null) || ! is_int($payload['pagination']['total_count'] ?? null)
                || ! array_is_list($payload['results']) || $payload['pagination']['total_count'] < 0) {
                throw $this->invalid();
            }
            foreach ($payload['results'] as $row) {
                if (! is_array($row) || ! is_string($row['symbol'] ?? null) || ! is_array($row['query_values'] ?? null)) {
                    throw $this->invalid();
                }
                foreach (['sector', 'sub_sector', 'industry', 'sub_industry'] as $field) {
                    if (isset($row['query_values'][$field]) && ! is_string($row['query_values'][$field])) {
                        throw $this->invalid();
                    }
                }
            }

            return ['results' => $payload['results'], 'pagination' => $payload['pagination']];
        }, ttl: 86400, namespace: 'peer-fundamentals-v1');
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) && is_finite((float) $value) ? (float) $value : null;
    }

    private function invalid(): MarketDataUnavailable
    {
        return new MarketDataUnavailable('incomplete_population', 502, 'Kelengkapan seluruh populasi peer belum dapat dipastikan. Coba lagi; subset tidak dipakai untuk nilai.');
    }
}
