<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type TripType 'TRIP_TYPE_UNSPECIFIED'|'ROUND_TRIP'|'ONE_WAY'
 */
enum TripTypeEnum: string
{
    case UNSPECIFIED = 'TRIP_TYPE_UNSPECIFIED';
    case ROUND_TRIP = 'ROUND_TRIP';
    case ONE_WAY = 'ONE_WAY';
}
