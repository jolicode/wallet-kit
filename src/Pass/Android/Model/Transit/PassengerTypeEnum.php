<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type PassengerType 'PASSENGER_TYPE_UNSPECIFIED'|'SINGLE_PASSENGER'|'MULTIPLE_PASSENGERS'
 */
enum PassengerTypeEnum: string
{
    case UNSPECIFIED = 'PASSENGER_TYPE_UNSPECIFIED';
    case SINGLE_PASSENGER = 'SINGLE_PASSENGER';
    case MULTIPLE_PASSENGERS = 'MULTIPLE_PASSENGERS';
}
