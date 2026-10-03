<?php

namespace App\Modules\Company\Application;

use App\Modules\Company\Application\Contracts\FactAttestation;
use App\Modules\MarketData\Application\Contracts\CompanyAnalytics;

class GetCompanyAnalytics
{
    public function __construct(private readonly CompanyAnalytics $analytics, private readonly FactAttestation $attestation) {}

    public function execute(string $userId, string $symbol, string $section): array
    {
        $data = $this->analytics->load($userId, strtoupper($symbol), $section);

        return $data + ['receipt' => $this->attestation->seal($data, $section)];
    }
}
