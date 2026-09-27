<?php

namespace App\Modules\Comparison\Application\Contracts;

interface ComparisonSnapshotStore
{
    /**
     * @param  list<string>  $symbols
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(string $userId, string $title, array $symbols, array $payload, ?string $baseSnapshotId): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listForUser(string $userId): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findForUser(string $userId, string $snapshotId): ?array;
}
