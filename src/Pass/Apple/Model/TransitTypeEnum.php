<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type TransitType 'PKTransitTypeAir'|'PKTransitTypeBoat'|'PKTransitTypeBus'|'PKTransitTypeGeneric'|'PKTransitTypeTrain'
 */
enum TransitTypeEnum: string
{
    case Air = 'PKTransitTypeAir';
    case Boat = 'PKTransitTypeBoat';
    case Bus = 'PKTransitTypeBus';
    case Generic = 'PKTransitTypeGeneric';
    case Train = 'PKTransitTypeTrain';
}
