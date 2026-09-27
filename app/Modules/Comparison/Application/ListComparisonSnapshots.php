<?php

namespace App\Modules\Comparison\Application;

use App\Modules\Comparison\Application\Contracts\ComparisonSnapshotStore;

class ListComparisonSnapshots
{
    public function __construct(private readonly ComparisonSnapshotStore $snapshots) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(string $userId): array
    {
        return $this->snapshots->listForUser($userId);
    }
}
