<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception\Api;

use Jolicode\WalletKit\Exception\WalletKitException;

final class UnknownOperationTypeException extends \LogicException implements WalletKitException
{
    public function __construct(string $operationType, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf('Unknown operation type "%s".', $operationType), 0, $previous);
    }
}
