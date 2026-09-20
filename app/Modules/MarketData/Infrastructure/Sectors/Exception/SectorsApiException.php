<?php

namespace App\Modules\MarketData\Infrastructure\Sectors\Exception;

use RuntimeException;
use Throwable;

class SectorsApiException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $reason,
        private readonly ?int $providerStatus = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function missingApiKey(): self
    {
        return new self('Sectors API key is not configured.', 'missing_api_key');
    }

    public static function providerError(int $status): self
    {
        return new self(
            message: 'Sectors provider request failed.',
            reason: match ($status) {
                400 => 'provider_bad_request',
                401, 403 => 'provider_unauthorized',
                404 => 'provider_not_found',
                429 => 'provider_rate_limited',
                default => $status >= 500 ? 'provider_unavailable' : 'provider_error',
            },
            providerStatus: $status,
        );
    }

    public static function timeout(Throwable $previous): self
    {
        return new self('Sectors provider request timed out.', 'provider_timeout', previous: $previous);
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function providerStatus(): ?int
    {
        return $this->providerStatus;
    }
}
