<?php

namespace App\Modules\MarketData\Application\Exception;

use InvalidArgumentException;

class InvalidScreenerCriteriaException extends InvalidArgumentException
{
    public static function unsupportedField(string $field): self
    {
        return new self("Unsupported screener field [{$field}].");
    }

    public static function unsupportedOperator(string $operator): self
    {
        return new self("Unsupported screener operator [{$operator}].");
    }

    public static function invalidValue(string $field): self
    {
        return new self("Invalid screener value for field [{$field}].");
    }

    public static function invalidPagination(): self
    {
        return new self('Invalid screener pagination.');
    }
}
