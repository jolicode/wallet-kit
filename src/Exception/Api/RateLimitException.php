<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception\Api;

final class RateLimitException extends ApiResponseException
{
    public function __construct(
        string $responseBody,
        public readonly ?int $retryAfterSeconds = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(429, $responseBody, 'API rate limit exceeded (429).', $previous);
    }
}
