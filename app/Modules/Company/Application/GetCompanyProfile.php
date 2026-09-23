<?php

namespace App\Modules\Company\Application;

use App\Modules\MarketData\Application\Contracts\CompanyDirectory;

class GetCompanyProfile
{
    public function __construct(private readonly CompanyDirectory $directory) {}

    public function execute(string $userId, string $symbol): array
    {
        return $this->directory->profile($userId, strtoupper($symbol));
    }
}
