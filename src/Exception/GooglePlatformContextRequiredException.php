<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception;

final class GooglePlatformContextRequiredException extends \RuntimeException implements WalletKitException
{
    public function __construct(string $message = 'This operation requires a WalletPlatformContext Google slice.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
