<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception\Api;

use Jolicode\WalletKit\Exception\WalletKitException;

final class HttpRequestException extends \RuntimeException implements WalletKitException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
