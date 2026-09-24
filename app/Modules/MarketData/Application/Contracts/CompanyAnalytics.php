<?php

namespace App\Modules\MarketData\Application\Contracts;

interface CompanyAnalytics
{
    /** @return array{symbol:string,section:string,rows:list<array<string,mixed>>,fetchedAt:string} */
    public function load(string $userId, string $symbol, string $section): array;
}
