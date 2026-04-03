<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type FareClass 'FARE_CLASS_UNSPECIFIED'|'ECONOMY'|'FIRST'|'BUSINESS'
 */
enum FareClassEnum: string
{
    case UNSPECIFIED = 'FARE_CLASS_UNSPECIFIED';
    case ECONOMY = 'ECONOMY';
    case FIRST = 'FIRST';
    case BUSINESS = 'BUSINESS';
}
