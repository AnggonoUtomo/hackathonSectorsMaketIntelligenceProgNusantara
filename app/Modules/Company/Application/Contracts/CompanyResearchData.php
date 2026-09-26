<?php

namespace App\Modules\Company\Application\Contracts;

interface CompanyResearchData
{
    /** @return array{company: array, financials: array} */
    public function load(string $userId, string $symbol): array;
}
