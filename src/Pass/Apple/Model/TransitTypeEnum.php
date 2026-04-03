<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type TransitType 'PKTransitTypeAir'|'PKTransitTypeBoat'|'PKTransitTypeBus'|'PKTransitTypeGeneric'|'PKTransitTypeTrain'
 */
enum TransitTypeEnum: string
{
    case AIR = 'PKTransitTypeAir';
    case BOAT = 'PKTransitTypeBoat';
    case BUS = 'PKTransitTypeBus';
    case GENERIC = 'PKTransitTypeGeneric';
    case TRAIN = 'PKTransitTypeTrain';
}
