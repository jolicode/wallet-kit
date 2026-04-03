<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type FareClass 'FARE_CLASS_UNSPECIFIED'|'ECONOMY'|'FIRST'|'BUSINESS'
 */
enum FareClassEnum: string
{
    case Unspecified = 'FARE_CLASS_UNSPECIFIED';
    case Economy = 'ECONOMY';
    case First = 'FIRST';
    case Business = 'BUSINESS';
}
