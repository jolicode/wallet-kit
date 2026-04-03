<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type SecurityAnimationAlias 'ANIMATION_UNSPECIFIED'|'FOIL_SHIMMER'
 */
enum SecurityAnimationTypeEnum: string
{
    case Unspecified = 'ANIMATION_UNSPECIFIED';
    case FoilShimmer = 'FOIL_SHIMMER';
}
