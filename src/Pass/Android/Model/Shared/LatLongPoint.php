<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type LatLongPointType array{latitude?: float, longitude?: float}
 */
class LatLongPoint
{
    public function __construct(
        public ?float $latitude = null,
        public ?float $longitude = null,
    ) {
    }
}
