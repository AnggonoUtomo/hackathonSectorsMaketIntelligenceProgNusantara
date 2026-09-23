<?php

namespace App\Modules\Screening\Application;

use App\Modules\MarketData\Application\Contracts\CompanyDirectory;

class SearchCompanies
{
    public function __construct(private readonly CompanyDirectory $directory) {}

    public function execute(string $userId, string $query, int $page, int $perPage): array
    {
        return $this->directory->search($userId, trim($query), max(1, $page), max(1, min(25, $perPage)));
    }
}
