<?php

namespace App\Modules\Company\Application;

use App\Modules\Company\Application\Contracts\CompanyResearchData;
use App\Modules\MarketData\Application\Contracts\CompanyAnalytics;
use App\Modules\MarketData\Application\Contracts\CompanyDirectory;

class GetCompanyResearchData implements CompanyResearchData
{
    public function __construct(private readonly CompanyDirectory $directory, private readonly CompanyAnalytics $analytics) {}

    public function load(string $userId, string $symbol): array
    {
        $company = $this->directory->profile($userId, $symbol);
        $financials = $this->analytics->load($userId, $symbol, 'financials');

        // Only documented field semantics are eligible for research calculations.
        $financials['currency'] = 'IDR';
        $financials['basis'] = ['revenue' => 'quarterly', 'earnings' => 'quarterly', 'total_equity' => 'instant'];

        return compact('company', 'financials');
    }
}
