<?php

namespace App\Modules\Comparison\Application\Contracts;

interface ComparisonSnapshotStore
{
    public function rename(string $userId, string $snapshotId, string $title): bool;

    public function delete(string $userId, string $snapshotId): bool;

    /**
     * @param  list<string>  $symbols
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(string $userId, string $title, array $symbols, array $payload, ?string $baseSnapshotId): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listForUser(string $userId, string $query = '', int $page = 1): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findForUser(string $userId, string $snapshotId): ?array;
}
