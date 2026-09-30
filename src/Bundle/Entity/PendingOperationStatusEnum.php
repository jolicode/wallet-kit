<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Entity;

enum PendingOperationStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case FAILED = 'failed';
}
