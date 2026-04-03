<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception;

/**
 * Thrown when internal state should be impossible given constructor validation (defensive).
 */
final class WalletKitInvariantViolationException extends \RuntimeException implements WalletKitException
{
    public function __construct(string $message = 'Wallet Kit internal invariant violated.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
