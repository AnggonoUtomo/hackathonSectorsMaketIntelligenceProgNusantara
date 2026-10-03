<?php

namespace App\Modules\Comparison\Infrastructure\Persistence;

use App\Modules\Comparison\Application\Contracts\ComparisonSnapshotStore;

class EloquentComparisonSnapshotStore implements ComparisonSnapshotStore
{
    public function rename(string $userId, string $snapshotId, string $title): bool
    {
        return ComparisonSnapshot::query()->where('user_id', $userId)->whereKey($snapshotId)->update(['title' => $title]) > 0;
    }

    public function delete(string $userId, string $snapshotId): bool
    {
        return ComparisonSnapshot::query()->where('user_id', $userId)->whereKey($snapshotId)->delete() > 0;
    }

    public function create(string $userId, string $title, array $symbols, array $payload, ?string $baseSnapshotId): array
    {
        $base = $baseSnapshotId !== null ? $this->findModelForUser($userId, $baseSnapshotId) : null;
        $snapshot = ComparisonSnapshot::query()->create([
            'user_id' => $userId,
            'title' => $title,
            'symbols' => $symbols,
            'payload' => $payload,
            'version' => $base !== null ? $base->version + 1 : 1,
            'created_from_snapshot_id' => $base?->id,
        ]);

        return $this->map($snapshot);
    }

    public function listForUser(string $userId, string $query = '', int $page = 1): array
    {
        $records = ComparisonSnapshot::query()
            ->where('user_id', $userId)
            ->when($query !== '', fn ($builder) => $builder->where('title', 'like', '%'.addcslashes($query, '%_\\').'%'))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(15, ['id', 'title', 'symbols', 'version', 'created_at'], 'page', $page);

        return ['items' => $records->map(fn ($snapshot) => [
            'id' => $snapshot->id, 'title' => $snapshot->title, 'symbols' => $snapshot->symbols,
            'version' => $snapshot->version, 'createdAt' => $snapshot->created_at?->toIso8601String(),
        ])->all(), 'total' => $records->total(), 'page' => $records->currentPage(), 'lastPage' => $records->lastPage()];
    }

    public function findForUser(string $userId, string $snapshotId): ?array
    {
        $snapshot = $this->findModelForUser($userId, $snapshotId);

        return $snapshot === null ? null : $this->map($snapshot);
    }

    private function findModelForUser(string $userId, string $snapshotId): ?ComparisonSnapshot
    {
        return ComparisonSnapshot::query()
            ->where('user_id', $userId)
            ->whereKey($snapshotId)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function map(ComparisonSnapshot $snapshot): array
    {
        return [
            'id' => $snapshot->id,
            'title' => $snapshot->title,
            'symbols' => $snapshot->symbols,
            'payload' => $snapshot->payload,
            'version' => $snapshot->version,
            'createdFromSnapshotId' => $snapshot->created_from_snapshot_id,
            'createdAt' => $snapshot->created_at?->toIso8601String(),
            'updatedAt' => $snapshot->updated_at?->toIso8601String(),
        ];
    }
}
