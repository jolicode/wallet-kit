<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception;

final class GoogleWalletPairNotAvailableException extends \RuntimeException implements WalletKitException
{
    public function __construct(string $message = 'Google Wallet pair is not available: WalletPlatformContext has no Google slice.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
