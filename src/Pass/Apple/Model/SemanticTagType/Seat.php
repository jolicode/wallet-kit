<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType;

/**
 * @phpstan-type SeatType array{seatDescription?: string, seatIdentifier?: string, seatNumber?: string, seatRow?: string, seatSection?: string, seatType?: string}
 */
class Seat
{
    public function __construct(
        public ?string $seatDescription = null,
        public ?string $seatIdentifier = null,
        public ?string $seatNumber = null,
        public ?string $seatRow = null,
        public ?string $seatSection = null,
        public ?string $seatType = null,
    ) {
    }
}
