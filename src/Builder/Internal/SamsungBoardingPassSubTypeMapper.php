<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Internal;

use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardSubTypeEnum;

final class SamsungBoardingPassSubTypeMapper
{
    public static function fromTransitType(TransitTypeEnum $transitType): CardSubTypeEnum
    {
        return match ($transitType) {
            TransitTypeEnum::BUS => CardSubTypeEnum::BUSES,
            TransitTypeEnum::RAIL, TransitTypeEnum::TRAM => CardSubTypeEnum::TRAINS,
            TransitTypeEnum::FERRY, TransitTypeEnum::OTHER, TransitTypeEnum::UNSPECIFIED => CardSubTypeEnum::OTHERS,
        };
    }
}
