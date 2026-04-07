<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type RotatingBarcodeTotpDetailsTotpParametersType array{key?: string, valueLength?: int}
 */
class RotatingBarcodeTotpDetailsTotpParameters
{
    public function __construct(
        public ?string $key = null,
        public ?int $valueLength = null,
    ) {
    }
}
