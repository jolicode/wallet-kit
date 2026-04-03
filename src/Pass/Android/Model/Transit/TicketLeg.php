<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;

/**
 * @phpstan-import-type TicketSeatType from TicketSeat
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type TicketLegType array{originStationCode?: string, originName?: LocalizedStringType, destinationStationCode?: string, destinationName?: LocalizedStringType, departureDateTime?: string, arrivalDateTime?: string, fareName?: LocalizedStringType, carriage?: string, platform?: string, zone?: string, ticketSeat?: TicketSeatType, ticketSeats?: list<TicketSeatType>, transitOperatorName?: LocalizedStringType, transitTerminusName?: LocalizedStringType}
 */
class TicketLeg
{
    /**
     * @param list<TicketSeat>|null $ticketSeats
     */
    public function __construct(
        public ?string $originStationCode = null,
        public ?LocalizedString $originName = null,
        public ?string $destinationStationCode = null,
        public ?LocalizedString $destinationName = null,
        public ?string $departureDateTime = null,
        public ?string $arrivalDateTime = null,
        public ?LocalizedString $fareName = null,
        public ?string $carriage = null,
        public ?string $platform = null,
        public ?string $zone = null,
        public ?TicketSeat $ticketSeat = null,
        public ?array $ticketSeats = null,
        public ?LocalizedString $transitOperatorName = null,
        public ?LocalizedString $transitTerminusName = null,
    ) {
    }
}
