<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type State 'ACTIVE'|'COMPLETED'|'EXPIRED'|'INACTIVE'
 */
enum StateEnum: string
{
    case ACTIVE = 'ACTIVE';
    case COMPLETED = 'COMPLETED';
    case EXPIRED = 'EXPIRED';
    case INACTIVE = 'INACTIVE';
}
