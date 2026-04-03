<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type TransitType 'TRANSIT_TYPE_UNSPECIFIED'|'BUS'|'RAIL'|'TRAM'|'FERRY'|'OTHER'
 */
enum TransitTypeEnum: string
{
    case Unspecified = 'TRANSIT_TYPE_UNSPECIFIED';
    case Bus = 'BUS';
    case Rail = 'RAIL';
    case Tram = 'TRAM';
    case Ferry = 'FERRY';
    case Other = 'OTHER';
}
