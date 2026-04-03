<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type SecurityAnimationAlias 'ANIMATION_UNSPECIFIED'|'FOIL_SHIMMER'
 * @phpstan-type SecurityAnimationType SecurityAnimationAlias
 */
enum SecurityAnimationTypeEnum: string
{
    case UNSPECIFIED = 'ANIMATION_UNSPECIFIED';
    case FOIL_SHIMMER = 'FOIL_SHIMMER';
}
