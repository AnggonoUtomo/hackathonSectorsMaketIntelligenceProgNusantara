<?php

namespace App\Modules\Intelligence\Application;

use App\Modules\Intelligence\Application\Contracts\ScoreEvidence;

final class PruneResearchEvidence
{
    public function __construct(private readonly ScoreEvidence $evidence) {}

    public function execute(\DateTimeImmutable $now): int
    {
        return $this->evidence->pruneExpired($now);
    }
}
