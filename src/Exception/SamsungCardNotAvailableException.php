<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception;

final class SamsungCardNotAvailableException extends \RuntimeException implements WalletKitException
{
    public function __construct(string $message = 'Samsung Card is not available: WalletPlatformContext has no Samsung slice.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
