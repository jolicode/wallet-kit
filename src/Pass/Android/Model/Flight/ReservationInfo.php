<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

/**
 * @phpstan-import-type FrequentFlyerInfoType from FrequentFlyerInfo
 *
 * @phpstan-type ReservationInfoType array{confirmationCode?: string, eticketNumber?: string, frequentFlyerInfo?: FrequentFlyerInfoType}
 */
class ReservationInfo
{
    public function __construct(
        public ?string $confirmationCode = null,
        public ?string $eticketNumber = null,
        public ?FrequentFlyerInfo $frequentFlyerInfo = null,
    ) {
    }
}
