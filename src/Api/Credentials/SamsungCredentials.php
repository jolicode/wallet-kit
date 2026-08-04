<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Credentials;

use Jolicode\WalletKit\Api\Samsung\SamsungRegionEnum;

final readonly class SamsungCredentials
{
    public function __construct(
        public string $partnerId,
        public string $privateKeyPath,
        public ?string $serviceId = null,
        public SamsungRegionEnum $region = SamsungRegionEnum::EU,
    ) {
    }
}
