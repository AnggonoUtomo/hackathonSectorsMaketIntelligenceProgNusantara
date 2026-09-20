<?php

namespace App\Modules\MarketData\Infrastructure\Credit;

use RuntimeException;

class CreditReservationException extends RuntimeException
{
    public static function budgetExhausted(): self
    {
        return new self('Sectors credit budget is exhausted.');
    }

    public static function dailyQuotaExhausted(): self
    {
        return new self('Daily Sectors credit quota is exhausted.');
    }

    public static function retryLimitExceeded(): self
    {
        return new self('Sectors retry attempt limit exceeded.');
    }
}
