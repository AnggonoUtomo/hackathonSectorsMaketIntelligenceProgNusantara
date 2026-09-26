<?php

namespace App\Modules\Research\Application;

use App\Modules\Company\Application\Contracts\CompanyResearchData;
use App\Modules\Research\Domain\ResearchSummary;

class GetResearchSummary
{
    public function __construct(private readonly CompanyResearchData $data, private readonly ResearchSummary $summary) {}

    public function execute(string $userId, string $symbol): array
    {
        $data = $this->data->load($userId, strtoupper($symbol));

        return $this->summary->build($data['company'], $data['financials'], now()->toDateTimeImmutable());
    }
}
