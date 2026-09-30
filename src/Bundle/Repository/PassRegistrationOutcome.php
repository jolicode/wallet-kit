<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Repository;

/**
 * Outcome of a device registration attempt on the Apple Web Service.
 */
enum PassRegistrationOutcome
{
    /**
     * A new registration row was inserted.
     */
    case Created;

    /**
     * The device was already registered and re-registered with a rotated push token.
     */
    case TokenUpdated;

    /**
     * Device and push token were identical: idempotent no-op, nothing was written.
     */
    case Unchanged;
}
