<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

/**
 * @phpstan-type BoardingDoor 'BOARDING_DOOR_UNSPECIFIED'|'FRONT'|'BACK'
 */
enum BoardingDoorEnum: string
{
    case UNSPECIFIED = 'BOARDING_DOOR_UNSPECIFIED';
    case FRONT = 'FRONT';
    case BACK = 'BACK';
}
