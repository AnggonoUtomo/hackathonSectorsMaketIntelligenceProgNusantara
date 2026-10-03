<?php

namespace App\Modules\Intelligence\Application;

use App\Modules\Intelligence\Application\Contracts\ScoreEvidence;
use App\Modules\Intelligence\Domain\ResearchPriorityCalculator;
use App\Modules\Intelligence\Domain\ValuationPeerPlanner;
use App\Modules\MarketData\Application\Contracts\PeerFinancialData;
use App\Modules\MarketData\Application\Contracts\PeerMarketData;
use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;

final class CalculateResearchPriority
{
    public function __construct(private readonly PeerFinancialData $source, private readonly ScoreEvidence $evidence,
        private readonly ResearchPriorityCalculator $calculator,
        private readonly PeerMarketData $market) {}

    public function execute(string $userId, string $symbol, bool $includeMarket = false): array
    {
        $input = $this->source->fetch($userId, strtoupper($symbol));
        $result = $this->calculator->calculate($input['symbol'], $input['companies']);
        if ($includeMarket) {
            foreach ($input['companies'] as &$company) {
                $company['valuationGroups'] = [];
            }
            unset($company);
            foreach ((new ValuationPeerPlanner)->plans($input['symbol'], $input['companies']) as $plan) {
                try {
                    $quotes = $this->market->fetch($userId, $plan['symbols']);
                } catch (MarketDataUnavailable $e) {
                    $result['marketNotice'] = $e->getMessage();
                    break;
                }
                foreach ($input['companies'] as &$company) {
                    if (isset($quotes[$company['symbol']])) {
                        $company['market'] = $quotes[$company['symbol']];
                    }
                    if ($company['symbol'] === $input['symbol']) {
                        $company['valuationGroups'][] = ['period' => $plan['period'], 'level' => $plan['level'], 'group' => $plan['group']];
                    }
                }
                unset($company);
                $result = $this->calculator->calculate($input['symbol'], $input['companies']);
                if ($result['components'][2]['metrics'][0]['percentile'] !== null && $result['components'][2]['metrics'][1]['percentile'] !== null) {
                    break;
                }
            }
            $deadlines = array_column(array_filter(array_column($input['companies'], 'market')), 'expiresAt');
            if ($deadlines !== []) {
                $expiry = new \DateTimeImmutable($input['expiresAt']);
                foreach ($deadlines as $deadline) {
                    $expiry = min($expiry, new \DateTimeImmutable($deadline));
                }
                $input['expiresAt'] = $expiry->setTimezone(new \DateTimeZone('UTC'))->format(DATE_ATOM);
            }
        }
        // Availability messages are part of the evidence identity, not mutable annotations.
        if (isset($result['marketNotice'])) {
            $input['marketNotice'] = $result['marketNotice'];
        }

        return $this->evidence->save($input, $result)['result'];
    }
}
