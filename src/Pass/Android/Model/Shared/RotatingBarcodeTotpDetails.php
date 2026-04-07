<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type RotatingBarcodeTotpDetailsTotpParametersType from RotatingBarcodeTotpDetailsTotpParameters
 *
 * @phpstan-type RotatingBarcodeTotpDetailsType array{totpParameters?: list<RotatingBarcodeTotpDetailsTotpParametersType>, periodMillis?: string}
 */
class RotatingBarcodeTotpDetails
{
    /**
     * @param list<RotatingBarcodeTotpDetailsTotpParameters>|null $totpParameters
     */
    public function __construct(
        public ?array $totpParameters = null,
        public ?string $periodMillis = null,
    ) {
    }
}
