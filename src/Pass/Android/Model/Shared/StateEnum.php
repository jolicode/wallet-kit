<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type State 'ACTIVE'|'COMPLETED'|'EXPIRED'|'INACTIVE'
 */
enum StateEnum: string
{
    case Active = 'ACTIVE';
    case Completed = 'COMPLETED';
    case Expired = 'EXPIRED';
    case Inactive = 'INACTIVE';
}
