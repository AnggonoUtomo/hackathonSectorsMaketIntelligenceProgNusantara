<?php

namespace App\Modules\Intelligence\Application\Contracts;

interface PeerResearch
{
    public function candidates(string $evidenceId): ?array;
}
