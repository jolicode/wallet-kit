<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Transit;

/**
 * @phpstan-type ActivationState 'UNKNOWN_STATE'|'NOT_ACTIVATED'|'ACTIVATED'
 */
enum ActivationStateEnum: string
{
    case UnknownState = 'UNKNOWN_STATE';
    case NotActivated = 'NOT_ACTIVATED';
    case Activated = 'ACTIVATED';
}
