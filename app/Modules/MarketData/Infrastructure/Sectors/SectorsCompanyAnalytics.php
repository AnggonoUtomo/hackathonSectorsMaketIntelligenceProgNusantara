<?php

namespace App\Modules\MarketData\Infrastructure\Sectors;

use App\Modules\MarketData\Application\Contracts\CompanyAnalytics;
use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use DateTimeImmutable;

class SectorsCompanyAnalytics implements CompanyAnalytics
{
    private const FINANCIAL_FIELDS = ['revenue', 'earnings', 'gross_profit', 'operating_cash_flow',
        'free_cash_flow', 'total_assets', 'total_equity', 'total_liabilities', 'total_debt'];

    private const SECTOR_FIELDS = ['interest_income', 'net_interest_income', 'gross_loan', 'net_loan',
        'total_deposit', 'current_account', 'savings_account', 'time_deposit'];

    public function __construct(private readonly CachedSectorsRequest $requests) {}

    public function load(string $userId, string $symbol, string $section): array
    {
        if (! preg_match('/\A[A-Z0-9]{4}\z/', $symbol) || ! in_array($section, ['prices', 'financials', 'valuation'], true)) {
            throw new MarketDataUnavailable('invalid_query', 422, 'Pilihan data perusahaan tidak valid.');
        }
        $end = now('Asia/Jakarta')->startOfDay();
        [$endpoint, $parameters, $cost, $ttl] = match ($section) {
            'prices' => ['daily/'.$symbol, ['start' => $end->copy()->subDays(89)->toDateString(), 'end' => $end->toDateString()], 1, 3600],
            'financials' => ['financials/quarterly/'.$symbol, ['n_quarters' => 4, 'approx' => 'false'], 4, 86400],
            'valuation' => ['company/report/'.$symbol, ['sections' => 'valuation'], 1, 3600],
        };

        return $this->requests->remember($userId, $endpoint, $parameters, function (array $payload) use ($symbol, $section, $parameters): array {
            if ($section === 'valuation') {
                $this->assertSymbol($payload, $symbol);
                $rows = $payload['valuation']['historical_valuation'] ?? null;
            } else {
                $rows = $payload;
            }
            if (! is_array($rows) || ! array_is_list($rows)) {
                throw $this->invalid();
            }
            $mapped = [];
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    throw $this->invalid();
                }
                if ($section !== 'valuation') {
                    $this->assertSymbol($row, $symbol);
                }
                $date = $section === 'valuation' ? (string) ($row['year'] ?? '') : ($row['date'] ?? null);
                if (! is_string($date) || ($section === 'valuation'
                    ? ! preg_match('/\A(?:19|20)\d{2}\z/', $date)
                    : ! $this->validDate($date)) || isset($mapped[$date])) {
                    throw $this->invalid();
                }
                $item = ['date' => $date];
                $fields = match ($section) {
                    'prices' => ['close', 'open', 'high', 'low', 'volume'],
                    'financials' => self::FINANCIAL_FIELDS,
                    'valuation' => ['pe', 'pb', 'ps', 'pcf', 'enterprise_to_ebitda'],
                };
                foreach ($fields as $field) {
                    $item[$field] = $this->number($row[$field] ?? null);
                }
                if ($section === 'financials') {
                    $sector = $row['financials_sector_metrics'] ?? [];
                    if (! is_array($sector)) {
                        throw $this->invalid();
                    }
                    foreach (self::SECTOR_FIELDS as $field) {
                        $item[$field] = $this->number($sector[$field] ?? null);
                    }
                }
                $mapped[$date] = $item;
            }
            ksort($mapped);

            return ['symbol' => $symbol, 'section' => $section, 'rows' => array_values($mapped)]
                + ($section === 'prices' ? ['range' => $parameters] : []);
        }, $cost, $ttl, 'analytics-v1');
    }

    private function assertSymbol(array $row, string $symbol): void
    {
        if (! is_string($row['symbol'] ?? null) || strtoupper(preg_replace('/\.JK$/i', '', $row['symbol'])) !== $symbol) {
            throw $this->invalid();
        }
    }

    private function validDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date;
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) && is_finite((float) $value) ? (float) $value : null;
    }

    private function invalid(): MarketDataUnavailable
    {
        return new MarketDataUnavailable('invalid_response', 502, 'Format seri data Sectors belum dapat dibaca.');
    }
}
