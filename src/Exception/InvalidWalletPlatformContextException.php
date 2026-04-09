<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception;

final class InvalidWalletPlatformContextException extends \InvalidArgumentException implements WalletKitException
{
    public static function missingPlatformSlice(): self
    {
        return new self('WalletPlatformContext requires at least one of apple, google, or samsung.');
    }

    public static function googleIssuerNameRequiredWhenAppleAbsent(): self
    {
        return new self('GoogleWalletContext requires a non-empty issuerName when no Apple context is provided.');
    }
}
