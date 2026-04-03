<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception;

final class ApplePassNotAvailableException extends \RuntimeException implements WalletKitException
{
    public function __construct(string $message = 'Apple Pass is not available: WalletPlatformContext has no Apple slice.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
