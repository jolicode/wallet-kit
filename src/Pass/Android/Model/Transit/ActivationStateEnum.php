<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type ActivationState 'UNKNOWN_STATE'|'NOT_ACTIVATED'|'ACTIVATED'
 */
enum ActivationStateEnum: string
{
    case UNKNOWN_STATE = 'UNKNOWN_STATE';
    case NOT_ACTIVATED = 'NOT_ACTIVATED';
    case ACTIVATED = 'ACTIVATED';
}
