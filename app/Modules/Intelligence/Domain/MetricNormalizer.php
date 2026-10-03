<?php

namespace App\Modules\Intelligence\Domain;

final class MetricNormalizer
{
    public function normalize(array $company): array
    {
        $result = [];
        foreach ($company['periods']['annual'] as $period) {
            $facts = $company['annual'][$period] ?? [];
            foreach (['roe' => 'equity', 'roa' => 'assets'] as $key => $denominator) {
                $value = $this->number($facts[$key] ?? null);
                $result[$key][] = $this->observation($value !== null && $this->positive($facts[$denominator] ?? null) ? $value * 100 : null,
                    (string) $period, 'annual', '%', $facts, $company);
            }
            $ratios = ($company['kind'] ?? null) === 'bank'
                ? ['car' => ['capital', 'riskWeightedAssets', 100], 'npl' => ['nonPerformingLoan', 'grossLoan', 100]]
                : ['der' => ['debt', 'equity', 1], 'currentRatio' => ['currentAssets', 'currentLiabilities', 1]];
            foreach ($ratios as $key => [$numerator, $denominator, $scale]) {
                $value = $this->ratio($facts[$numerator] ?? null, $facts[$denominator] ?? null, $scale);
                if (($company['kind'] ?? null) === 'financial' || ($value !== null && $value < 0)) {
                    $value = null;
                }
                $result[$key][] = $this->observation($value, (string) $period, 'annual', $scale === 100 ? '%' : 'x', $facts, $company);
            }
        }
        foreach ($company['periods']['quarterly'] as $period) {
            [$quarter, $year] = explode('-', $period);
            $previous = $quarter.'-'.((int) $year - 1);
            foreach (['revenueGrowth' => 'revenue', 'earningsGrowth' => 'earnings'] as $key => $field) {
                $current = $company['quarterly'][$period][$field] ?? null;
                $prior = $company['quarterly'][$previous][$field] ?? null;
                $ratio = $this->ratio($current, $prior);
                $result[$key][] = $this->observation($ratio === null ? null : ($ratio - 1) * 100,
                    $period, 'quarterly-yoy', '%', ['current' => $current, 'previous' => $prior, 'previousPeriod' => $previous], $company);
            }
        }
        if (isset($company['market'])) {
            $company['valuation'] = [];
            foreach ($company['periods']['quarterly'] as $period) {
                [$quarter, $year] = explode('-', $period);
                $q = (int) substr($quarter, 1);
                $y = (int) $year;
                $ttm = 0.0;
                for ($i = 0; $i < 4; $i++) {
                    $earnings = $this->number($company['quarterly']['Q'.$q.'-'.$y]['earnings'] ?? null);
                    if ($earnings === null) {
                        $ttm = null;
                        break;
                    }
                    $ttm += $earnings;
                    if (--$q === 0) {
                        $q = 4;
                        $y--;
                    }
                }
                foreach ($company['market']['rows'] as $quote) {
                    $company['valuation'][] = ['period' => $period, 'priceDate' => $quote['date'], 'marketCap' => $quote['marketCap'],
                        'price' => $quote['close'], 'earningsTTM' => $ttm, 'equity' => $company['quarterly'][$period]['equity'] ?? null,
                        'marketFetchedAt' => $company['market']['fetchedAt']];
                }
            }
        }
        foreach (['pe' => 'earningsTTM', 'pb' => 'equity'] as $key => $denominator) {
            foreach ($company['valuation'] ?? [] as $facts) {
                $value = $this->positive($facts['price'] ?? null) && $this->positive($facts['marketCap'] ?? null)
                    && ! empty($facts['priceDate']) ? $this->ratio($facts['marketCap'], $facts[$denominator] ?? null) : null;
                $result[$key][] = $this->observation($value, $facts['period'], $key === 'pe' ? 'TTM' : 'MRQ', 'x', $facts, $company)
                    + ['priceDate' => $facts['priceDate'] ?? null];
            }
            $result[$key] ??= [$this->observation(null, '', $key === 'pe' ? 'TTM' : 'MRQ', 'x', [], $company,
                'Tanggal harga, periode laporan dan denominator valuasi belum selaras pada seluruh peer.')];
        }
        $prices = $company['prices'] ?? [];
        $valid = ($company['pricesAdjusted'] ?? false) === true && count($prices) === 21;
        $dates = array_column($prices, 'date');
        $sorted = $dates;
        sort($sorted);
        $valid = $valid && count(array_unique($dates)) === 21 && $dates === $sorted;
        foreach ($prices as $price) {
            $valid = $valid && $this->positive($price['close'] ?? null);
        }
        $ratio = $valid ? $this->ratio($prices[20]['close'], $prices[0]['close']) : null;
        $result['momentum'] = [$this->observation($ratio === null ? null : ($ratio - 1) * 100,
            $valid ? $dates[0].'/'.$dates[20] : '', '20-sessions-adjusted', '%', $prices, $company,
            'Konsistensi aksi korporasi dan 21 penutupan yang selaras belum terverifikasi.')];

        return $result;
    }

    private function observation(?float $value, string $period, string $basis, string $unit, array $inputs, array $company, ?string $reason = null): array
    {
        $value = $value !== null && is_finite($value) ? $value : null;

        return compact('value', 'period', 'basis', 'unit', 'inputs') + [
            'fetchedAt' => $company['fetchedAt'] ?? null,
            'reason' => $value === null ? ($reason ?? 'Input tidak tersedia atau denominator tidak positif pada periode ini.') : null,
        ];
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) && is_finite((float) $value) ? (float) $value : null;
    }

    private function positive(mixed $value): bool
    {
        return ($this->number($value) ?? 0) > 0;
    }

    private function ratio(mixed $numerator, mixed $denominator, float $scale = 1): ?float
    {
        $numerator = $this->number($numerator);

        return $numerator !== null && $this->positive($denominator) ? $numerator / (float) $denominator * $scale : null;
    }
}
