<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Auth;

final class CachedToken implements TokenInterface
{
    public function __construct(
        private readonly string $accessToken,
        private readonly \DateTimeImmutable $expiresAt,
    ) {
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() >= $this->expiresAt;
    }
}
