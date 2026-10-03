<?php

namespace App\Modules\Intelligence\Infrastructure\Persistence;

use App\Modules\Intelligence\Application\Contracts\ScoreEvidence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class DatabaseScoreEvidence implements ScoreEvidence
{
    public function save(array $input, array $result): array
    {
        $json = json_encode($input, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
        $fingerprint = hash('sha256', $result['formulaVersion'].':'.$json);
        $existing = DB::table('intelligence_evidence')->where('fingerprint', $fingerprint)->first();
        if ($existing) {
            return $this->map($existing);
        }
        $id = (string) Str::ulid();
        $result += ['evidenceId' => $id, 'fetchedAt' => $input['fetchedAt'], 'expiresAt' => $input['expiresAt'],
            'source' => $input['source'], 'populationCount' => count($input['companies'])];
        DB::table('intelligence_evidence')->insertOrIgnore(['id' => $id, 'fingerprint' => $fingerprint,
            'symbol' => $result['symbol'], 'formula_version' => $result['formulaVersion'], 'input' => $json,
            'result' => json_encode($result, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION), 'created_at' => now()]);

        return $this->map(DB::table('intelligence_evidence')->where('fingerprint', $fingerprint)->first());
    }

    public function find(string $id): ?array
    {
        $record = DB::table('intelligence_evidence')->where('id', $id)->first();

        return $record ? $this->map($record) : null;
    }

    private function map(object $record): array
    {
        return ['id' => $record->id, 'input' => json_decode($record->input, true, flags: JSON_THROW_ON_ERROR),
            'result' => json_decode($record->result, true, flags: JSON_THROW_ON_ERROR)];
    }

    public function pruneExpired(\DateTimeImmutable $now): int
    {
        $deleted = 0;
        // Saved comparisons contain independent full copies of input and result.
        DB::table('intelligence_evidence')->where('created_at', '<', $now->modify('-30 days'))
            ->orderBy('id')->chunkById(100, function ($records) use ($now, &$deleted) {
                $ids = [];
                foreach ($records as $record) {
                    $input = json_decode($record->input, true, flags: JSON_THROW_ON_ERROR);
                    if (isset($input['expiresAt']) && new \DateTimeImmutable($input['expiresAt']) < $now) {
                        $ids[] = $record->id;
                    }
                }
                $deleted += DB::table('intelligence_evidence')->whereIn('id', $ids)->delete();
            });

        return $deleted;
    }
}
