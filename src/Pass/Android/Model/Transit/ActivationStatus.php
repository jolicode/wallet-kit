<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-import-type ActivationState from ActivationStateEnum
 *
 * @phpstan-type ActivationStatusType array{state?: ActivationState}
 */
class ActivationStatus
{
    public function __construct(
        public ?ActivationStateEnum $state = null,
    ) {
    }
}
