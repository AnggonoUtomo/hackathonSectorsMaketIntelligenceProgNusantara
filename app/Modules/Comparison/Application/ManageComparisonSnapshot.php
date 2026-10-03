<?php

namespace App\Modules\Comparison\Application;

use App\Modules\Comparison\Application\Contracts\ComparisonSnapshotStore;

final class ManageComparisonSnapshot
{
    public function __construct(private readonly ComparisonSnapshotStore $store) {}

    public function rename(string $userId, string $id, string $title): bool
    {
        return $this->store->rename($userId, $id, trim($title));
    }

    public function delete(string $userId, string $id): bool
    {
        return $this->store->delete($userId, $id);
    }
}
