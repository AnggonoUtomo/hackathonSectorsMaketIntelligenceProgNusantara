<?php

namespace App\Modules\MarketData\Application\Contracts;

interface CompanyDirectory
{
    /** @return array{items: list<array<string, mixed>>, total: int, page: int, perPage: int, fetchedAt: string} */
    public function search(string $userId, string $query, int $page, int $perPage): array;

    /** @return array<string, mixed> */
    public function profile(string $userId, string $symbol): array;
}
