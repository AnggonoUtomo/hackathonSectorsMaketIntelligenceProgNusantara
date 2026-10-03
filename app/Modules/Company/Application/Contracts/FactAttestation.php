<?php

namespace App\Modules\Company\Application\Contracts;

interface FactAttestation
{
    public function seal(array $facts, string $kind): string;

    public function open(string $receipt, string $kind, string $symbol): array;
}
