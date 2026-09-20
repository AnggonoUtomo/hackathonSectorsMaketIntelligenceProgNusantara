<?php

namespace App\Modules\MarketData\Application\DTO;

class CreditReservation
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $usageDate,
        public readonly string $endpoint,
        public readonly int $estimatedCredits,
        public readonly int $attempt,
        public readonly string $status,
        public readonly string $correlationId,
    ) {}
}
