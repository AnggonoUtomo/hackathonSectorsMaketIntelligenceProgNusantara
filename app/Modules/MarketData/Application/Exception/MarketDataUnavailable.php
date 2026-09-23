<?php

namespace App\Modules\MarketData\Application\Exception;

use RuntimeException;

class MarketDataUnavailable extends RuntimeException
{
    public function __construct(public readonly string $reason, public readonly int $status, string $message)
    {
        parent::__construct($message);
    }

    public function details(): array
    {
        return ['reason' => $this->reason, 'message' => $this->getMessage()];
    }
}
