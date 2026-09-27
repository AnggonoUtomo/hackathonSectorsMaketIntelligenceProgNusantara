<?php

namespace App\Modules\Comparison\Application;

use App\Modules\Comparison\Application\Contracts\ComparisonSnapshotStore;

class GetComparisonSnapshot
{
    public function __construct(private readonly ComparisonSnapshotStore $snapshots) {}

    /**
     * @return array<string, mixed>|null
     */
    public function execute(string $userId, string $snapshotId): ?array
    {
        return $this->snapshots->findForUser($userId, $snapshotId);
    }
}
