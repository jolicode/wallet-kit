<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type TransitType 'TRANSIT_TYPE_UNSPECIFIED'|'BUS'|'RAIL'|'TRAM'|'FERRY'|'OTHER'
 */
enum TransitTypeEnum: string
{
    case UNSPECIFIED = 'TRANSIT_TYPE_UNSPECIFIED';
    case BUS = 'BUS';
    case RAIL = 'RAIL';
    case TRAM = 'TRAM';
    case FERRY = 'FERRY';
    case OTHER = 'OTHER';
}
