<?php

namespace App\Modules\MarketData\Infrastructure\Sectors;

use App\Modules\MarketData\Application\Contracts\PeerMarketData;
use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use App\Modules\MarketData\Infrastructure\Cache\MarketDataCache;
use App\Modules\MarketData\Infrastructure\Credit\CreditReservationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

final class SectorsPeerMarketData implements PeerMarketData
{
    public function __construct(private readonly CachedSectorsRequest $requests, private readonly CreditReservationService $credits,
        private readonly MarketDataCache $cache) {}

    public function fetch(string $userId, array $symbols): array
    {
        $parameters = ['start' => now('Asia/Jakarta')->subDay()->toDateString(), 'end' => now('Asia/Jakarta')->toDateString()];
        $cold = 0;
        foreach ($symbols as $symbol) {
            if (! preg_match('/\A[A-Z0-9]{4}\z/', $symbol)) {
                throw $this->invalid();
            }
            if (! Cache::has($this->cache->key('peer-market-v1/daily/'.$symbol, $parameters))) {
                $cold++;
            }
        }
        if ($cold > $this->credits->remaining($userId)) {
            throw new MarketDataUnavailable('credit_limit', 429, 'Valuasi membutuhkan hingga '.$cold.' credit untuk seluruh peer. Sisa kuota belum cukup; analisis fundamental tetap tersedia.');
        }
        $results = [];
        foreach ($symbols as $symbol) {
            $result = $this->requests->remember($userId, 'daily/'.$symbol, $parameters, function (array $payload) use ($symbol): array {
                if (! array_is_list($payload)) {
                    throw $this->invalid();
                }
                $rows = [];
                foreach ($payload as $row) {
                    if (! is_array($row) || ! is_string($row['symbol'] ?? null) || preg_replace('/\.JK$/i', '', $row['symbol']) !== $symbol
                        || ! is_string($row['date'] ?? null) || ! preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $row['date'])) {
                        throw $this->invalid();
                    }
                    $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $row['date']);
                    if (! $date || $date->format('Y-m-d') !== $row['date'] || isset($rows[$row['date']])) {
                        throw $this->invalid();
                    }
                    $rows[$row['date']] = ['date' => $row['date'], 'close' => $this->number($row['close'] ?? null), 'marketCap' => $this->number($row['market_cap'] ?? null)];
                }
                krsort($rows);

                return ['rows' => array_values($rows)];
            }, namespace: 'peer-market-v1');
            // A date-only observation cannot prove a more precise closing timestamp.
            $result['rows'] = array_values(array_filter($result['rows'], fn ($r) => CarbonImmutable::parse($r['date'], 'Asia/Jakarta')->betweenIncluded(now('Asia/Jakarta')->subDay(), now('Asia/Jakarta'))));
            $expiry = CarbonImmutable::parse($result['fetchedAt'])->addHour();
            foreach ($result['rows'] as $row) {
                $expiry = $expiry->min(CarbonImmutable::parse($row['date'], 'Asia/Jakarta')->addDay());
            }
            $result['expiresAt'] = $expiry->utc()->toIso8601String();
            $results[$symbol] = $result;
        }

        return $results;
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) && is_finite((float) $value) ? (float) $value : null;
    }

    private function invalid(): MarketDataUnavailable
    {
        return new MarketDataUnavailable('invalid_response', 502, 'Tanggal, identitas atau angka pasar belum dapat diverifikasi.');
    }
}
