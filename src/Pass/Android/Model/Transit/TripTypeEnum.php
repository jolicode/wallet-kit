<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type TripType 'TRIP_TYPE_UNSPECIFIED'|'ROUND_TRIP'|'ONE_WAY'
 */
enum TripTypeEnum: string
{
    case Unspecified = 'TRIP_TYPE_UNSPECIFIED';
    case RoundTrip = 'ROUND_TRIP';
    case OneWay = 'ONE_WAY';
}
