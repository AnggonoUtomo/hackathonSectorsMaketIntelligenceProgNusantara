<?php

namespace App\Modules\MarketData\Application\DTO;

readonly class ScreenerCompany
{
    /**
     * @param  array<string, mixed>  $queryValues
     */
    public function __construct(
        public string $symbol,
        public string $name,
        public ?string $sector = null,
        public ?string $subSector = null,
        public array $queryValues = [],
    ) {}
}
