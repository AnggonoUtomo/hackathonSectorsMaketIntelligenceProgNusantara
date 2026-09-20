<?php

namespace App\Modules\MarketData\Application\DTO;

readonly class ScreenerResult
{
    /**
     * @param  list<ScreenerCompany>  $companies
     */
    public function __construct(
        public array $companies,
        public int $page,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}
}
