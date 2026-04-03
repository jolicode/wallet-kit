<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

/**
 * @phpstan-import-type FlightCarrierType from FlightCarrier
 *
 * @phpstan-type FlightHeaderType array{carrier?: FlightCarrierType, flightNumber?: string, operatingCarrier?: FlightCarrierType, operatingFlightNumber?: string, flightNumberDisplayOverride?: string}
 */
class FlightHeader
{
    public function __construct(
        public ?FlightCarrier $carrier = null,
        public ?string $flightNumber = null,
        public ?FlightCarrier $operatingCarrier = null,
        public ?string $operatingFlightNumber = null,
        public ?string $flightNumberDisplayOverride = null,
    ) {
    }
}
