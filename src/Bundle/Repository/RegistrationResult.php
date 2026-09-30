<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Repository;

/**
 * Result of a device registration attempt on the Apple Web Service:
 * what happened, and the push token the registration used to carry.
 */
final class RegistrationResult
{
    public function __construct(
        public readonly PassRegistrationOutcome $outcome,
        public readonly ?string $previousPushToken,
    ) {
    }

    public static function created(): self
    {
        return new self(PassRegistrationOutcome::Created, null);
    }

    public static function rotated(string $previousPushToken): self
    {
        return new self(PassRegistrationOutcome::TokenUpdated, $previousPushToken);
    }

    public static function unchanged(): self
    {
        return new self(PassRegistrationOutcome::Unchanged, null);
    }
}
