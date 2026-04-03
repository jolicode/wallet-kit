<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception;

final class ApplePlatformContextRequiredException extends \RuntimeException implements WalletKitException
{
    public function __construct(string $message = 'This operation requires a WalletPlatformContext Apple slice.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
