<?php

namespace App\Modules\Intelligence\Application\Contracts;

interface ScoreEvidence
{
    public function save(array $input, array $result): array;

    public function find(string $id): ?array;

    public function pruneExpired(\DateTimeImmutable $now): int;
}
