<?php

namespace App\Modules\Company\Application;

use App\Modules\MarketData\Application\Contracts\CompanyAnalytics;

class GetCompanyAnalytics
{
    public function __construct(private readonly CompanyAnalytics $analytics) {}

    public function execute(string $userId, string $symbol, string $section): array
    {
        return $this->analytics->load($userId, strtoupper($symbol), $section);
    }
}
