<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Samsung;

final class SamsungApiResponse
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly int $statusCode,
        private readonly array $data,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
