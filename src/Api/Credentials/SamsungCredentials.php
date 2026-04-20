<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Credentials;

final readonly class SamsungCredentials
{
    public function __construct(
        public string $partnerId,
        public string $privateKeyPath,
        public ?string $serviceId = null,
    ) {
    }
}
