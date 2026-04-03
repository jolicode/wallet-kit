<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType;

/**
 * @phpstan-type SemanticLocationType array{latitude: float, longitude: float}
 */
class SemanticLocation
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
    }
}
