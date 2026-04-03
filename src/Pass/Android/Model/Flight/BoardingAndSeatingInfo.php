<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;

/**
 * @phpstan-import-type ImageType from Image
 * @phpstan-import-type LocalizedStringType from LocalizedString
 * @phpstan-import-type BoardingDoor from BoardingDoorEnum
 *
 * @phpstan-type BoardingAndSeatingInfoType array{boardingGroup?: string, seatNumber?: string, seatClass?: string, boardingPrivilegeImage?: ImageType, boardingPosition?: string, sequenceNumber?: string, boardingDoor?: BoardingDoor, seatAssignment?: LocalizedStringType}
 */
class BoardingAndSeatingInfo
{
    public function __construct(
        public ?string $boardingGroup = null,
        public ?string $seatNumber = null,
        public ?string $seatClass = null,
        public ?Image $boardingPrivilegeImage = null,
        public ?string $boardingPosition = null,
        public ?string $sequenceNumber = null,
        public ?BoardingDoorEnum $boardingDoor = null,
        public ?LocalizedString $seatAssignment = null,
    ) {
    }
}
