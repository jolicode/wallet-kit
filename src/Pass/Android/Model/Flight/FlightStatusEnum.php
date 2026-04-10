<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Flight;

/**
 * @phpstan-type FlightStatus 'FLIGHT_STATUS_UNSPECIFIED'|'SCHEDULED'|'ACTIVE'|'LANDED'|'CANCELLED'|'REDIRECTED'|'DIVERTED'
 */
enum FlightStatusEnum: string
{
    case UNSPECIFIED = 'FLIGHT_STATUS_UNSPECIFIED';
    case SCHEDULED = 'SCHEDULED';
    case ACTIVE = 'ACTIVE';
    case LANDED = 'LANDED';
    case CANCELLED = 'CANCELLED';
    case REDIRECTED = 'REDIRECTED';
    case DIVERTED = 'DIVERTED';
}
