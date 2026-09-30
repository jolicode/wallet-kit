<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Apple;

final class ApnsPushResponse
{
    public function __construct(
        private readonly string $pushToken,
        private readonly int $statusCode,
        private readonly ?string $errorReason = null,
        private readonly ?string $apnsId = null,
    ) {
    }

    /**
     * Records a transport-level failure (connection dropped, timeout…) so one
     * failing token never breaks a whole batch.
     */
    public static function transportFailure(string $pushToken, string $message): self
    {
        return new self($pushToken, 0, \sprintf('transport error: %s', $message));
    }

    public function isSuccessful(): bool
    {
        return 200 === $this->statusCode;
    }

    /**
     * Server-side/transport failure: 5xx responses or a transport-level failure.
     */
    public function isFailed(): bool
    {
        return 0 === $this->statusCode || $this->statusCode >= 500;
    }

    public function isDeviceTokenInactive(): bool
    {
        return 410 === $this->statusCode;
    }

    public function isRateLimited(): bool
    {
        return 429 === $this->statusCode;
    }

    public function getPushToken(): string
    {
        return $this->pushToken;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorReason(): ?string
    {
        return $this->errorReason;
    }

    public function getApnsId(): ?string
    {
        return $this->apnsId;
    }
}
