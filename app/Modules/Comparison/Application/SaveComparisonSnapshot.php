<?php

namespace App\Modules\Comparison\Application;

use App\Modules\Comparison\Application\Contracts\ComparisonSnapshotStore;

class SaveComparisonSnapshot
{
    public function __construct(private readonly ComparisonSnapshotStore $snapshots) {}

    /**
     * @param  list<string>  $symbols
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function execute(string $userId, ?string $title, array $symbols, array $payload, ?string $baseSnapshotId): array
    {
        $symbols = array_values(array_unique(array_map(
            fn (string $symbol): string => preg_replace('/\.JK$/i', '', strtoupper(trim($symbol))) ?? '',
            $symbols,
        )));
        $title = trim((string) $title);
        if ($title === '') {
            $title = 'Perbandingan '.implode(', ', $symbols);
        }

        return $this->snapshots->create($userId, $title, $symbols, $payload, $baseSnapshotId);
    }
}
