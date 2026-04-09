<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type State 'STATE_UNSPECIFIED'|'ACTIVE'|'COMPLETED'|'EXPIRED'|'INACTIVE'
 */
enum StateEnum: string
{
    case UNSPECIFIED = 'STATE_UNSPECIFIED';
    case ACTIVE = 'ACTIVE';
    case COMPLETED = 'COMPLETED';
    case EXPIRED = 'EXPIRED';
    case INACTIVE = 'INACTIVE';
}
