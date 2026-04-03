<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type LocationType array{latitude: float, longitude: float, altitude?: float, relevantText?: string}
 */
class Location
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public ?float $altitude = null,
        public ?string $relevantText = null,
    ) {
    }
}
