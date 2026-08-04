<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Auth;

interface TokenInterface
{
    public function getAccessToken(): string;

    public function isExpired(): bool;
}
