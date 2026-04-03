<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;

/**
 * @phpstan-import-type FareClass from FareClassEnum
 * @phpstan-import-type LocalizedStringType from LocalizedString
 *
 * @phpstan-type TicketSeatType array{fareClass?: FareClass, customFareClass?: LocalizedStringType, coach?: string, seat?: string, seatAssignment?: LocalizedStringType}
 */
class TicketSeat
{
    public function __construct(
        public ?FareClassEnum $fareClass = null,
        public ?LocalizedString $customFareClass = null,
        public ?string $coach = null,
        public ?string $seat = null,
        public ?LocalizedString $seatAssignment = null,
    ) {
    }
}
