<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;

/**
 * @phpstan-import-type LocalizedStringType from LocalizedString
 * @phpstan-import-type ImageType from Image
 *
 * @phpstan-type FlightCarrierType array{carrierIataCode?: string, airlineName?: LocalizedStringType, airlineLogo?: ImageType, airlineAllianceLogo?: ImageType, wideAirlineLogo?: ImageType}
 */
class FlightCarrier
{
    public function __construct(
        public ?string $carrierIataCode = null,
        public ?LocalizedString $airlineName = null,
        public ?Image $airlineLogo = null,
        public ?Image $airlineAllianceLogo = null,
        public ?Image $wideAirlineLogo = null,
    ) {
    }
}
