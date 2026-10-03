<?php

namespace App\Modules\MarketData\Application\Contracts;

interface PeerFinancialData
{
    /** Complete, normalized population, independent of discovery filters. */
    public function fetch(string $userId, string $symbol): array;
}
