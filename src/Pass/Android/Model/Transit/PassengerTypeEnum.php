<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type PassengerType 'PASSENGER_TYPE_UNSPECIFIED'|'SINGLE_PASSENGER'|'MULTIPLE_PASSENGERS'
 */
enum PassengerTypeEnum: string
{
    case Unspecified = 'PASSENGER_TYPE_UNSPECIFIED';
    case SinglePassenger = 'SINGLE_PASSENGER';
    case MultiplePassengers = 'MULTIPLE_PASSENGERS';
}
