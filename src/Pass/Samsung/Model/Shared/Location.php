<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Model\Shared;

/**
 * @phpstan-type SamsungLocationType array{lat: float, lng: float, address?: string, name?: string}
 */
class Location
{
    public function __construct(
        public float $lat,
        public float $lng,
        public ?string $address = null,
        public ?string $name = null,
    ) {
    }
}
