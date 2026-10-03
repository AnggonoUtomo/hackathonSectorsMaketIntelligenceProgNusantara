<?php

namespace App\Modules\MarketData\Application\Contracts;

interface PeerMarketData
{
    /** All requested companies, or an explicit failure. Never a truncated peer sample. */
    public function fetch(string $userId, array $symbols): array;
}
