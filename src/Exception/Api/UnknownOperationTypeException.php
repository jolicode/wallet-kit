<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception\Api;

final class UnknownOperationTypeException extends \RuntimeException
{
    public function __construct(string $operationType, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf('Unknown operation type "%s".', $operationType), 0, $previous);
    }
}
