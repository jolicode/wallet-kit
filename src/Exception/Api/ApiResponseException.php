<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception\Api;

use Jolicode\WalletKit\Exception\WalletKitException;

class ApiResponseException extends \RuntimeException implements WalletKitException
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $responseBody,
        string $message = '',
        ?\Throwable $previous = null,
    ) {
        if ('' === $message) {
            $message = \sprintf('API request failed with status code %d.', $statusCode);
        }

        parent::__construct($message, $statusCode, $previous);
    }
}
