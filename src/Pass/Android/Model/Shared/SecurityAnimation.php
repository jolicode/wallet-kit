<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type SecurityAnimationAlias from SecurityAnimationTypeEnum
 *
 * @phpstan-type SecurityAnimationType array{animationType?: SecurityAnimationAlias}
 */
class SecurityAnimation
{
    public function __construct(
        public ?SecurityAnimationTypeEnum $animationType = null,
    ) {
    }
}
